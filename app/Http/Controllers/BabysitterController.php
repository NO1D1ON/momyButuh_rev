<?php

namespace App\Http\Controllers;

use App\Models\Babysitter;
use Illuminate\Http\Request;

class BabysitterController extends Controller
{
    // Menampilkan daftar semua babysitter
    public function index()
    {
        $babysitters = Babysitter::latest()->paginate(10);
        return view('admin.babysitters.index', compact('babysitters'));
    }

    // Menampilkan form untuk membuat babysitter baru
    public function create()
    {
        return view('admin.babysitters.create');
    }

    // Menyimpan babysitter baru ke database
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:babysitters,email',
            'phone_number' => 'nullable|string',
            'birth_date' => 'required|date',
            'address' => 'nullable|string',
            'bio' => 'nullable|string',
            'rate_per_hour' => 'required|integer|min:0',
        ]);

        Babysitter::create($validatedData);

        return redirect()->route('babysitters.index')->with('success', 'Data babysitter berhasil ditambahkan.');
    }

    // Menampilkan form untuk mengedit data babysitter
    public function edit(Babysitter $babysitter)
    {
        return view('admin.babysitters.edit', compact('babysitter'));
    }

    // Memperbarui data babysitter di database
    public function update(Request $request, Babysitter $babysitter)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:babysitters,email,' . $babysitter->id,
            'phone_number' => 'nullable|string',
            'birth_date' => 'required|date',
            'address' => 'nullable|string',
            'bio' => 'nullable|string',
            'rate_per_hour' => 'required|integer|min:0',
            'is_available' => 'sometimes|boolean',
        ]);

        $babysitter->update($validatedData);

        return redirect()->route('babysitters.index')->with('success', 'Data babysitter berhasil diperbarui.');
    }

    // Menghapus data babysitter dari database
    public function destroy(Babysitter $babysitter)
    {
        $babysitter->delete();

        return redirect()->route('babysitters.index')->with('success', 'Data babysitter berhasil dihapus.');
    }
}