<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Event;
use Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Gate;

class TicketController extends Controller
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

    /**
     * Capacity is informational only; existing orders and API clients keep
     * their current behaviour. Shows whether new tickets would overfill the
     * event's "people allowed" setting.
     */
    private function capacityContext(Event $event, ?Ticket $ticket = null): array
    {
        $query = Ticket::where('event_id', $event->id)
            ->where([
                ['is_deleted', 0],
                ['status', 1],
            ]);

        if ($ticket) {
            $query->where('id', '!=', $ticket->id);
        }

        $capacity = (int) ($event->people ?? 0);
        $allocated = (int) $query->sum('quantity');

        return [
            'eventCapacity' => $capacity,
            'allocatedTickets' => $allocated,
            'remainingCapacity' => max(0, $capacity - $allocated),
            'capacityExceeded' => $capacity > 0 && $allocated > $capacity,
        ];
    }

    public function index($id, $name)
    {
        abort_if(Gate::denies('ticket_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $event = Event::findOrFail($id);
        $this->authorizeEventAccess($event);

        $ticket = Ticket::where([['event_id', $id], ['is_deleted', 0]])->orderBy('id', 'DESC')->get();
        return view('admin.ticket.index', compact('ticket', 'event') + $this->capacityContext($event));
    }

    public function create($id)
    {
        abort_if(Gate::denies('ticket_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $event = Event::findOrFail($id);
        $this->authorizeEventAccess($event);

        return view('admin.ticket.create', compact('event') + $this->capacityContext($event));
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('ticket_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request->validate([
            'event_id' => 'bail|required',
            'name' => 'bail|required',
            'quantity' => 'bail|required',
            'start_time' => 'bail|required',
            'end_time' => 'bail|required',
            'type' => 'bail|required',
            'ticket_per_order' => 'bail|required',
            'price' => 'bail|required_if:type,paid',
        ]);
        $event = Event::findOrFail($request->event_id);
        $this->authorizeEventAccess($event);

        $data = $request->all();
        if ($request->type == "free") {
            $data['price'] = 0;
        }
        $data['ticket_number'] = chr(rand(65, 90)) . chr(rand(65, 90)) . '-' . rand(999, 10000);
        $data['event_id'] = $event->id;
        $data['user_id'] = $event->user_id;
        Ticket::create($data);
        return redirect($event->id . '/' . preg_replace('/\s+/', '-', $event->name) . '/tickets')->withStatus(__('Ticket is added successfully.'));
    }

    public function show(Ticket $ticket)
    {
    }

    public function edit($id)
    {
        abort_if(Gate::denies('ticket_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $ticket = Ticket::findOrFail($id);
        $event = Event::findOrFail($ticket->event_id);
        $this->authorizeEventAccess($event);

        return view('admin.ticket.edit', compact('ticket', 'event') + $this->capacityContext($event, $ticket));
    }

    public function update(Request $request, $id)
    {
        abort_if(Gate::denies('ticket_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request->validate([
            'name' => 'bail|required',
            'quantity' => 'bail|required',
            'start_time' => 'bail|required',
            'end_time' => 'bail|required',
            'type' => 'bail|required',
            'ticket_per_order' => 'bail|required',
            'price' => 'bail|required_if:type,paid',
        ]);
        $ticket = Ticket::findOrFail($id);
        $event = Event::findOrFail($ticket->event_id);
        $this->authorizeEventAccess($event);

        $data = $request->all();
        if ($request->type == "free") {
            $data['price'] = 0;
        }
        unset($data['event_id'], $data['user_id']);
        $ticket->update($data);
        return redirect($event->id . '/' . preg_replace('/\s+/', '-', $event->name) . '/tickets')->withStatus(__('Ticket is updated successfully.'));
    }

    public function destroy(Ticket $ticket)
    {
    }

    public function deleteTickets($id)
    {
        $ticket = Ticket::findOrFail($id);
        $event = Event::findOrFail($ticket->event_id);
        $this->authorizeEventAccess($event);

        try {
            $ticket->update(['is_deleted' => 1]);
            return true;
        } catch (\Throwable $th) {
            return response('Data is Connected with other Data', 400);
        }
    }
}
