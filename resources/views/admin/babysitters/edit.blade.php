@extends('layouts.app')

@section('title', 'Edit Babysitter')

@section('content')
<div class="p-4">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Edit Data Babysitter</h1>

    <div class="p-6 bg-white rounded-lg shadow">
        <form action="{{ route('babysitters.update', $babysitter->id) }}" method="POST">
            @csrf
            @method('PUT')
            {{-- Sertakan _form.blade.php di sini --}}
            @include('admin.babysitters._form', ['is_edit' => true])
        </form>
    </div>
</div>
@endsection