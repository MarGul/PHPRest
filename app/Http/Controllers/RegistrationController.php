<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Meeting;
use App\User;

class RegistrationController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'meeting_id' => 'required|numeric',
            'user_id' => 'required|numeric'
        ]);

        $meeting_id = $request->input('meeting_id');
        $user_id = $request->input('user_id');

        $meeting = Meeting::findOrFail((int)$meeting_id);
        $user = User::findOrFail((int)$user_id);

        $message = [
            'message' => 'User is already registered for the meeting',
            'user' => $user,
            'meeting' => $meeting,
            'unregister' => [
                'href' => 'api/v1/meeting/registration/' . $meeting->id,
                'method' => 'DELETE'
            ]
        ];

        // If the user already exists for this meeting
        if ($meeting->users()->where('users.id', $user->id)->first()) {
            return response()->json($message, 404);
        }

        $user->meetings()->attach($meeting);

        $response = [
            'message' => 'User successfully registered for meeting',
            'meeting' => $meeting,
            'user' => $user,
            'unregister' => [
                'href' => 'api/v1/meeting/registration/' . $meeting->id,
                'method' => 'DELETE'
            ]
        ];

        return response()->json($response, 201);
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
        $meeting->users()->detach();

        $response = [
            'message' => 'User successfully unregistered for meeting',
            'meeting' => $meeting,
            'user' => 'tbd',
            'register' => [
                'href' => 'api/v1/meeting/registration',
                'method' => 'POST',
                'params' => 'user_id, meeting_id'
            ]
        ];

        return response()->json($response, 200);
    }
}
