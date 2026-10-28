@extends('layouts.layout')

@section('title', 'Cashier Shifts')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>⏱️ Cashier Shifts & Cash Reconciliation</h2>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Cashier</th>
                        <th>Status</th>
                        <th>Opened At</th>
                        <th>Closed At</th>
                        <th>Opening Float</th>
                        <th>Cash Sales</th>
                        <th>Card Sales</th>
                        <th>Expected Cash</th>
                        <th>Actual Cash</th>
                        <th>Discrepancy</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shifts as $shift)
                        @php
                            $diff = ($shift->closing_cash !== null && $shift->expected_cash !== null) 
                                ? ($shift->closing_cash - $shift->expected_cash) 
                                : null;
                        @endphp
                        <tr>
                            <td><strong>#{{ $shift->id }}</strong></td>
                            <td>{{ $shift->user->name ?? '-' }}</td>
                            <td>
                                @if($shift->status === 'open')
                                    <span class="badge bg-success">🟢 OPEN</span>
                                @else
                                    <span class="badge bg-secondary">⚫ CLOSED</span>
                                @endif
                            </td>
                            <td>{{ $shift->opened_at ? $shift->opened_at->format('Y-m-d H:i') : '-' }}</td>
                            <td>{{ $shift->closed_at ? $shift->closed_at->format('Y-m-d H:i') : 'In progress' }}</td>
                            <td>${{ number_format($shift->opening_cash, 2) }}</td>
                            <td>${{ number_format($shift->cash_sales, 2) }}</td>
                            <td>${{ number_format($shift->card_sales, 2) }}</td>
                            <td>${{ number_format($shift->expected_cash ?? ($shift->opening_cash + $shift->cash_sales), 2) }}</td>
                            <td>
                                @if($shift->closing_cash !== null)
                                    ${{ number_format($shift->closing_cash, 2) }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($diff !== null)
                                    @if($diff == 0)
                                        <span class="badge bg-success">$0.00 (Balanced)</span>
                                    @elseif($diff > 0)
                                        <span class="badge bg-info text-dark">+${{ number_format($diff, 2) }} (Over)</span>
                                    @else
                                        <span class="badge bg-danger">-${{ number_format(abs($diff), 2) }} (Shortage)</span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('shifts.report', $shift->id) }}" class="btn btn-sm btn-outline-primary">
                                    View Z-Report
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center py-4 text-muted">No shifts recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3 d-flex justify-content-center">
        {{ $shifts->links('layouts._pagination') }}
    </div>
@endsection
