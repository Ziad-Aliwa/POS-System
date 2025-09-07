@extends('layouts.layout')

@section('title', 'Customers')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Customers</h2>
        <a href="{{ route('customers.create') }}" class="btn btn-primary">Add Customer</a>
    </div>

    <form method="GET" action="{{ route('customers.index') }}" class="mb-3">
        <input type="text" name="search" class="form-control" placeholder="Search by name, phone, or email"
            value="{{ $search }}">
    </form>

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($customers as $customer)
                <tr>
                    <td>{{ $customer->id }}</td>
                    <td>{{ $customer->name }}</td>
                    <td>{{ $customer->phone }}</td>
                    <td>{{ $customer->email }}</td>
                    <td>
                        <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-sm btn-warning">Edit</a>

                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                            data-bs-target="#confirmDeleteModal" data-id="{{ $customer->id }}"
                            data-name="{{ $customer->name }}">
                            Delete
                        </button>
                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">No customers found.</td>
                </tr>
            @endforelse

            <!-- Modal -->
            <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <form method="POST" id="deleteCustomerForm">
                        @csrf
                        @method('DELETE')

                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Confirm Delete</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>

                            <div class="modal-body">
                                <p>Are you sure you want to delete this customer?</p>
                                <p><strong>Name:</strong> <span id="modalCustomerName"></span></p>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger">Yes, Delete</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </tbody>
    </table>

    <div class="d-flex justify-content-center">
        {{ $customers->appends(request()->query())->links('layouts._pagination') }}
    </div>
@endsection


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('confirmDeleteModal');
        const form = document.getElementById('deleteCustomerForm');
        const nameSpan = document.getElementById('modalCustomerName');

        modal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const customerId = button.getAttribute('data-id');
            const customerName = button.getAttribute('data-name');

            nameSpan.textContent = customerName;

            form.action = `{{ url('customers') }}/${customerId}`;
        });
    });
</script>
