<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Currency;

class CurrencyController extends Controller
{
    public function index()
    {
        $currencies = Currency::orderBy('code')->get();
        return view('admin.currency.index', compact('currencies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'             => 'required|string|max:10|unique:currencies,code',
            'name'             => 'required|string|max:100',
            'conversion_type'  => 'required|in:multiply,divide',
            'conversion_rate'  => 'required|numeric|min:0.000001',
            'is_base'          => 'nullable|boolean',
        ]);

        if ($request->boolean('is_base')) {
            Currency::where('is_base', true)->update(['is_base' => false]);
        }

        Currency::create($validated);

        return redirect()->route('admin.currency.index')->with('success', 'Currency berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $currency = Currency::findOrFail($id);

        $validated = $request->validate([
            'code'             => 'required|string|max:10|unique:currencies,code,' . $id,
            'name'             => 'required|string|max:100',
            'conversion_type'  => 'required|in:multiply,divide',
            'conversion_rate'  => 'required|numeric|min:0.000001',
            'is_base'          => 'nullable|boolean',
        ]);

        if ($request->boolean('is_base')) {
            Currency::where('is_base', true)->update(['is_base' => false]);
        }

        $currency->update($validated);

        return redirect()->route('admin.currency.index')->with('success', 'Currency berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $currency = Currency::findOrFail($id);

        if ($currency->is_base) {
            return redirect()->route('admin.currency.index')->with('error', 'Currency base tidak bisa dihapus!');
        }

        $currency->delete();

        return redirect()->route('admin.currency.index')->with('success', 'Currency berhasil dihapus!');
    }
}
