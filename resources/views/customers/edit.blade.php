@extends('layouts.layout')

@section('title', 'Edit Customer')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <h2 class="mb-4">Edit Customer</h2>

       

        <form action="{{ route('customers.update', $customer->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $customer->name) }}"
                       class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone', $customer->phone) }}"
                       class="form-control">
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', $customer->email) }}"
                       class="form-control">
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea name="address" id="address" rows="3"
                          class="form-control">{{ old('address', $customer->address) }}</textarea>
            </div>

            <button type="submit" class="btn btn-success w-100">Save Changes</button>
        </form>
    </div>
</div>
@endsection
