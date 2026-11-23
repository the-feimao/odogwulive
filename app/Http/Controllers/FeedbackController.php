<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Gate;

class FeedbackController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('feedback_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if (Auth::user()->hasRole('organization')) {
            $feedback = Feedback::where('user_id', Auth::user()->id)->with(['user'])->orderBy('id', 'DESC')->get();
        } elseif (Auth::user()->hasRole('admin')) {
            $feedback = Feedback::with(['user'])->orderBy('id', 'DESC')->get();
        } else {
            abort(403);
        }

        return view('admin.feedback.index', compact('feedback'));
    }

    public function create()
    {
        abort_if(Gate::denies('feedback_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        return view('admin.feedback.create');
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('feedback_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request->validate([
            'email' => 'bail|required',
            'message' => 'bail|required',
            'rate' => 'bail|required',
        ]);
        $data = $request->all();
        $data['user_id'] = Auth::user()->id;
        Feedback::create($data);
        return redirect()->route('feedback.index')->withStatus(__('Feedback is added successfully.'));
    }

    public function show(Feedback $feedback)
    {
    }

    public function edit(Feedback $feedback)
    {
        abort_if(Gate::denies('feedback_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if (Auth::user()->hasRole('organization') && (int) $feedback->user_id !== (int) Auth::user()->id) {
            abort(403);
        }
        return view('admin.feedback.edit', compact('feedback'));
    }

    public function update(Request $request, Feedback $feedback)
    {
        abort_if(Gate::denies('feedback_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if (Auth::user()->hasRole('organization') && (int) $feedback->user_id !== (int) Auth::user()->id) {
            abort(403);
        }

        $request->validate([
            'email' => 'bail|required',
            'message' => 'bail|required',
            'rate' => 'bail|required',
        ]);

        $data = $request->all();
        $data['user_id'] = $feedback->user_id;
        $feedback->update($data);
        return redirect()->route('feedback.index')->withStatus(__('Feedback is updated successfully.'));
    }

    public function destroy(Feedback $feedback)
    {
        abort_if(Gate::denies('feedback_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if (Auth::user()->hasRole('organization') && (int) $feedback->user_id !== (int) Auth::user()->id) {
            abort(403);
        }

        try {
            $feedback->delete();
            return true;
        } catch (\Throwable $th) {
            return response('Data is Connected with other Data', 400);
        }
    }
}
