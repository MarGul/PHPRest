<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Meeting;
use Carbon\Carbon;
use JWTAuth;

class MeetingController extends Controller
{

    public function __construct() {
        // Add the jwt.auth middleware to this controller on specific routes
        $this->middleware('jwt.auth', ['only' => [
            'store', 'update', 'destroy'
        ]]);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $meetings = Meeting::all();
        // Add the link to all the meetings
        foreach ($meetings as $meeting) {
            $meeting->view_meeting = [
                'href' => 'api/v1/meeting/' . $meeting->id,
                'method' => 'GET'
            ];
        }

        $response = [
            'msg' => 'List of all meetings',
            'meetings' => $meetings
        ];

        // HTTP Status code 200 = OK
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Because we set the X-Requested-With header to XMLHttpRequest laravel
        // will automatically send back a json response with appropriate status
        // code if the validation fails.
        $this->validate($request, [
            'title' => 'required',
            'description' => 'required',
            'time' => 'required|date_format:YmdHie'
        ]);

        // See if we can extract the user from the token
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $title = $request->input('title');
        $description = $request->input('description');
        $time = $request->input('time');
        $user_id = $user->id;

        $meeting = new Meeting([
            'title' => $title,
            'description' => $description,
            'time' => Carbon::createFromFormat('YmdHie', $time),
        ]);

        /*
            OBS! GET AN ERROR HERE BECAUSE TIME DOESNT HAVE A DEFAULT VALUE
         */

        if($meeting->save()) {
            $meeting->users()->attach($user_id);
            $meeting->view_meeting = [
                'href' => 'api/v1/meeting/' . $meeting->id,
                'method' => 'GET'
            ];

            $response = [
                'msg' => 'Meeting successfully created',
                'meeting' => $meeting
            ];

            // HTTP Status code 201 = Created
            return response()->json($response, 201);
        }

        // Failed to insert meeting into database
        return response()->json([
            'error' => true,
            'error_message' => 'Failed to insert meeting into database.'
        ], 404);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        
        // Laravel will eager load with users as well. The firstOrFail means that
        // laravel will automatically send back a 404 response if no Meeting was found.
        $meeting = Meeting::with('users')->where('id', (int)$id)->firstOrFail();
        $meeting->view_meetings = [
            'href' => 'api/v1/meeting/',
            'method' => 'GET'
        ];

        $response = [
            'message' => 'Meeting Information',
            'meeting' => $meeting
        ];

        return response()->json($response, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'title' => 'required',
            'description' => 'required',
            'time' => 'required|date_format:YmdHie'
        ]);

        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $title = $request->input('title');
        $description = $request->input('description');
        $time = $request->input('time');
        $user_id = $user->id;

        $meeting = [
            'title' => $title,
            'description' => $description,
            'time' => $time,
            'user_id' => $user_id,
            'view_meeting' => [
                'href' => 'api/v1/meeting/1',
                'method' => 'GET'
            ]
        ];

        $meeting = Meeting::with('users')->findOrFail((int)$id);

        // Only registered users for this meeting can update it.
        if (! $meeting->users()->where('users.id', $user_id)->first()) {
            return response()->json([
                'error' => true,
                'error_message' => 'User is not registered for the meeting. Update not successful.'
            ], 401);
        }

        $meeting->title = $title;
        $meeting->description = $description;
        $meeting->time = Carbon::createFromFormat('YmdHie', $time);

        if (! $meeting->update()) {
            return response()->json([
                'error' => true,
                'error_message' => 'Error trying to update resource.'
            ], 404);
        }

        $meeting->view_meeting = [
            'href' => 'api/v1/meeting/' . $meeting->id,
            'method' => 'GET'
        ];

        $response = [
            'message' => 'Meeting successfully updated',
            'meeting' => $meeting
        ];

        return response()->json($response, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        
        $meeting = Meeting::findOrFail((int)$id);
        
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Check to see that the user is registered for the meeting.
        if (! $meeting->users()->where('users.id', $user->id)->first()) {
            return response()->json([
                'error' => true,
                'error_message' => 'User is not registered for the meeting. Update not successful.'
            ], 401);
        }

        $meeting->users()->detach();

        if (! $meeting->delete()) {
            foreach ($user as $users) {
                $meeting->users->attach($user);
            }
            return response()->json([
                'error' => true,
                'error_message' => 'Meeting could not be deleted.'
            ], 404);
        }

        $response = [
            'message' => 'Meeting deleted',
            'meeting' => $meeting,
            'create_meeting' => [
                'href' => 'api/v1/meeting',
                'method' => 'POST',
                'params' => 'title, description, time'
            ]
        ];

        return response()->json($response, 200);
    }
}
