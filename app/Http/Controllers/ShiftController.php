<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Services\ShiftService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShiftController extends Controller
{
    protected ShiftService $shiftService;

    public function __construct(ShiftService $shiftService)
    {
        $this->shiftService = $shiftService;
    }

    public function index()
    {
        $shifts = Shift::with('user')->latest()->paginate(15);
        return view('shifts.index', compact('shifts'));
    }

    public function current()
    {
        $shift = $this->shiftService->getActiveShift(Auth::id());
        return response()->json([
            'has_active_shift' => (bool) $shift,
            'shift' => $shift,
        ]);
    }

    public function open(Request $request)
    {
        $request->validate([
            'opening_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $shift = $this->shiftService->openShift(
                Auth::id(),
                floatval($request->opening_cash),
                $request->notes
            );

            return redirect()->back()->with('success', "Shift #{$shift->id} opened successfully with \${$shift->opening_cash} opening cash.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function close(Request $request, Shift $shift)
    {
        if ($shift->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'closing_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $closed = $this->shiftService->closeShift(
                $shift,
                floatval($request->closing_cash),
                $request->notes
            );

            return redirect()->route('shifts.report', $closed->id)->with('success', 'Shift closed successfully. Here is the Z-Report.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function report(Shift $shift)
    {
        $shift->load(['user', 'orders.customer']);
        $diff = ($shift->closing_cash ?? 0) - ($shift->expected_cash ?? 0);
        return view('shifts.report', compact('shift', 'diff'));
    }
}
