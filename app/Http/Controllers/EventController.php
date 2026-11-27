<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\Order;
use App\Http\Controllers\AppHelper;
use App\Models\User;
use App\Models\AppUser;
use Carbon\Carbon;
use Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    private function authorizeEventAccess(Event $event): void
    {
        $user = Auth::user();

        if (!$user) {
            abort(403);
        }

        if ($user->hasRole('admin')) {
            return;
        }

        if ($user->hasRole('organization') && (int) $event->user_id === (int) $user->id) {
            return;
        }

        abort(403);
    }

    private function allowedScannerIds(): array
    {
        $query = User::role('scanner');

        if (! Auth::user()->hasRole('admin')) {
            $query->where('org_id', Auth::user()->id);
        }

        return $query->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    private function scannerAssignmentRules(array $allowedIds): array
    {
        return [
            'scanner_id' => ['bail', 'required', Rule::in($allowedIds)],
            'scanner_ids' => 'nullable|array',
            'scanner_ids.*' => [Rule::in($allowedIds)],
        ];
    }

    private function scannerIdsForEvent(Request $request): array
    {
        return collect([$request->input('scanner_id')])
            ->merge($request->input('scanner_ids', []))
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private function ticketManagerUrl(Event $event): string
    {
        return url($event->id.'/'.preg_replace('/\s+/', '-', $event->name).'/tickets');
    }

    public function index()
    {
        abort_if(Gate::denies('event_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if (Auth::user()->hasRole('admin')) {
            $events = Event::with(['category', 'organization', 'scanners'])->where('is_deleted', 0)->orderBy('id', 'DESC')->get();
        } elseif (Auth::user()->hasRole('organization')) {
            $events = Event::with(['category', 'organization', 'scanners'])->where([['user_id', Auth::user()->id], ['is_deleted', 0]])->orderBy('id', 'DESC')->get();
        } else {
            abort(403);
        }

        return view('admin.event.index', compact('events'));
    }

    public function create()
    {
        abort_if(Gate::denies('event_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $category = Category::where('status', 1)->orderBy('id', 'DESC')->get();
        $users = User::role('organization')->orderBy('id', 'DESC')->get();
        $scanner = collect();
        if (Auth::user()->hasRole('admin')) {
            $scanner = User::role('scanner')->orderBy('id', 'DESC')->get();
        } elseif (Auth::user()->hasRole('organization')) {
            $scanner = User::role('scanner')->where('org_id', Auth::user()->id)->orderBy('id', 'DESC')->get();
        }
        return view('admin.event.create', compact('category', 'users', 'scanner'));
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('event_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $allowedScanners = $this->allowedScannerIds();
        $request->validate(array_merge([
            'name' => 'bail|required',
            'image' => 'bail|required',
            'start_time' => 'bail|required',
            'end_time' => 'bail|required',
            'category_id' => 'bail|required',
            'type' => 'bail|required',
            'event_link' => 'bail|exclude_unless:type,online|required|url|max:2048|regex:/^https?:\/\//i',
            'address' => 'bail|required_if:type,offline',
            'lat' => 'bail|required_if:type,offline',
            'lang' => 'bail|required_if:type,offline',
            'status' => 'bail|required',
            'description' => 'bail|required',
            'people' => 'bail|required',
        ], $this->scannerAssignmentRules($allowedScanners)));
        $data = $request->all();
        unset($data['after_save'], $data['scanner_ids']);
        if ($request->input('type') !== 'online') {
            $data['event_link'] = null;
        }
        if ($request->hasFile('image')) {
            $data['image'] = (new AppHelper)->saveImage($request);
        }
        if (!Auth::user()->hasRole('admin')) {
            $data['user_id'] = Auth::user()->id;
        }
        $event = Event::create($data);
        $event->syncScanners($this->scannerIdsForEvent($request));

        if ($request->input('after_save') === 'tickets') {
            return redirect($this->ticketManagerUrl($event))
                ->withStatus(__('Event created. Add a ticket and set its price to start selling.'));
        }

        return redirect()->route('events.index')->withStatus(__('Event is added successfully.'));
    }

    public function show(Event $event)
    {
        $this->authorizeEventAccess($event);

        $event->load(['category', 'organization']);
        $event->ticket = Ticket::where([['event_id', $event->id], ['is_deleted', 0]])->orderBy('id', 'DESC')->get();
        $event->sales = Order::with(['customer:id,name', 'ticket:id,name'])->where('event_id', $event->id)->orderBy('id', 'DESC')->get();
        foreach ($event->ticket as $value) {
            $value->used_ticket = Order::where('ticket_id', $value->id)->sum('quantity');
        }
        return view('admin.event.view', compact('event'));
    }

    public function edit(Event $event)
    {
        abort_if(Gate::denies('event_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $this->authorizeEventAccess($event);

        $category = Category::where('status', 1)->orderBy('id', 'DESC')->get();
        $users = User::role('organization')->orderBy('id', 'DESC')->get();
        $scanner = collect();
        if (Auth::user()->hasRole('admin')) {
            $scanner = User::role('scanner')->orderBy('id', 'DESC')->get();
        } elseif (Auth::user()->hasRole('organization')) {
            $scanner = User::role('scanner')->where('org_id', Auth::user()->id)->orderBy('id', 'DESC')->get();
        }
        return view('admin.event.edit', compact('event', 'category', 'users', 'scanner'));
    }

    public function update(Request $request, Event $event)
    {
        abort_if(Gate::denies('event_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $this->authorizeEventAccess($event);

        $allowedScanners = $this->allowedScannerIds();
        $request->validate(array_merge([
            'name' => 'bail|required',
            'start_time' => 'bail|required',
            'end_time' => 'bail|required',
            'category_id' => 'bail|required',
            'type' => 'bail|required',
            'event_link' => 'bail|exclude_unless:type,online|required|url|max:2048|regex:/^https?:\/\//i',
            'address' => 'bail|required_if:type,offline',
            'lat' => 'bail|required_if:type,offline',
            'lang' => 'bail|required_if:type,offline',
            'status' => 'bail|required',
            'description' => 'bail|required',
            'people' => 'bail|required',
        ], $this->scannerAssignmentRules($allowedScanners)));
        $data = $request->all();
        unset($data['after_save'], $data['scanner_ids']);
        if ($request->input('type') !== 'online') {
            $data['event_link'] = null;
        }
        if (!Auth::user()->hasRole('admin')) {
            $data['user_id'] = Auth::user()->id;
        }
        if ($request->hasFile('image')) {
            (new AppHelper)->deleteFile($event->image);
            $data['image'] = (new AppHelper)->saveImage($request);
        }
        $event->update($data);
        $event->syncScanners($this->scannerIdsForEvent($request));

        if ($request->input('after_save') === 'tickets') {
            return redirect($this->ticketManagerUrl($event))
                ->withStatus(__('Event updated. Review your ticket prices.'));
        }

        return redirect()->route('events.index')->withStatus(__('Event is updated successfully.'));
    }

    public function destroy(Event $event)
    {
        $this->authorizeEventAccess($event);

        try {
            $event->update(['is_deleted' => 1, 'event_status' => 'Deleted']);
            Ticket::where('event_id', $event->id)->update(['is_deleted' => 1]);
            return true;
        } catch (\Throwable $th) {
            return response('Data is Connected with other Data', 400);
        }
    }

    public function getMonthEvent(Request $request)
    {
        $request->validate([
            'year' => 'bail|required',
            'month' => 'bail|required',
        ]);

        $day = Carbon::parse($request->year . '-' . $request->month . '-01')->daysInMonth;
        if (Auth::user()->hasRole('organization')) {
            $data = Event::whereBetween('start_time', [$request->year . "-" . $request->month . "-01 12:00", $request->year . "-" . $request->month . "-" . $day . "  23:59"])
                ->where([['status', 1], ['is_deleted', 0], ['user_id', Auth::user()->id]])
                ->orderBy('id', 'DESC')
                ->get();
        } elseif (Auth::user()->hasRole('admin')) {
            $data = Event::whereBetween('start_time', [$request->year . "-" . $request->month . "-01 12:00", $request->year . "-" . $request->month . "-" . $day . " 23:59"])
                ->where([['status', 1], ['is_deleted', 0]])->orderBy('id', 'DESC')->get();
        } else {
            abort(403);
        }
        foreach ($data as $value) {
            $value->tickets = Ticket::where([['event_id', $value->id], ['is_deleted', 0]])->sum('quantity');
            $value->sold_ticket = Order::where('event_id', $value->id)->sum('quantity');
            $value->day = $value->start_time->format('D');
            $value->date = $value->start_time->format('d');
            $value->average = $value->tickets == 0 ? 0 : $value->sold_ticket * 100 / $value->tickets;
        }
        return response()->json(['data' => $data, 'success' => true], 200);
    }

    public function eventGallery($id)
    {
        $data = Event::findOrFail($id);
        $this->authorizeEventAccess($data);

        return view('admin.event.gallery', compact('data'));
    }

    public function addEventGallery(Request $request)
    {
        $request->validate([
            'id' => 'bail|required',
        ]);

        $data = Event::findOrFail($request->id);
        $this->authorizeEventAccess($data);

        $gallery = $data->gallery != null ? array_filter(explode(',', $data->gallery)) : [];
        if ($request->hasFile('file')) {
            $image = $request->file('file');
            $name = uniqid() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('/images/upload');
            $image->move($destinationPath, $name);
            array_push($gallery, $name);
            $data->update(['gallery' => implode(',', $gallery)]);
        }
        return true;
    }

    public function removeEventImage($image, $id)
    {
        $data = Event::findOrFail($id);
        $this->authorizeEventAccess($data);

        $gallery = $data->gallery != null ? array_filter(explode(',', $data->gallery)) : [];
        if (count(array_keys($gallery, $image)) > 0) {
            if (($key = array_search($image, $gallery)) !== false) {
                unset($gallery[$key]);
            }
        }
        $data->gallery = implode(',', $gallery);
        $data->update();
        return redirect()->back();
    }
}
