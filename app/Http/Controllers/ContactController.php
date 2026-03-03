<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $query = Contact::where('tenant_id', $tenantId);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $contacts = $query->latest()->paginate(20);

        return view('contacts.index', compact('contacts'));
    }

    public function create()
    {
        return view('contacts.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'cpf' => 'nullable|string|max:14',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            'source' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $data['tenant_id'] = auth()->user()->tenant_id;

        $contact = Contact::create($data);

        return redirect()->route('contacts.show', $contact)->with('success', 'Contato criado.');
    }

    public function show(Contact $contact)
    {
        $contact->load(['conversations.channel', 'leads.assignedUser', 'tickets', 'appointments.vehicle']);

        return view('contacts.show', compact('contact'));
    }

    public function edit(Contact $contact)
    {
        return view('contacts.edit', compact('contact'));
    }

    public function update(Request $request, Contact $contact)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'cpf' => 'nullable|string|max:14',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            'source' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $contact->update($data);

        return redirect()->route('contacts.show', $contact)->with('success', 'Contato atualizado.');
    }
}
