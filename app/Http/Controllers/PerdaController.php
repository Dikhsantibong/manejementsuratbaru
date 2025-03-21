<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;    
use App\Models\Perda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Exports\PerdaExport;

class PerdaController extends Controller
{
    public function index(Request $request)
    {
        $query = Perda::query();
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_agenda', 'LIKE', "%{$search}%")
                  ->orWhere('no_surat', 'LIKE', "%{$search}%")
                  ->orWhere('perihal', 'LIKE', "%{$search}%")
                  ->orWhere('pengirim', 'LIKE', "%{$search}%");
            });
        }
        
        $perdas = $query->latest()->paginate(10);

        return view('draft-phd.perda.index', compact('perdas'));
    }

    public function create()
    {
        return view('draft-phd.perda.create'); // Pastikan Anda memiliki tampilan draft-phd/perda/create.blade.php
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'no_agenda' => 'required|string|max:255',
                'no_surat' => 'required|string|max:255',
                'pengirim' => 'required|string|max:255',    
                'tanggal_surat' => 'required|date',
                'tanggal_terima' => 'required|date',
                'perihal' => 'required|string|max:255',
                'lampiran' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
            ]);
        
            if ($request->hasFile('lampiran')) {
                $file = $request->file('lampiran');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('lampiran/perda', $fileName, 'public');
                $validated['lampiran'] = $path;
            }
            
            // Set status default to 'tercatat'
            $validated['status'] = 'tercatat';
            
            Perda::create($validated);

            return redirect()->route('draft-phd.perda.index')
                ->with('success', 'Perda berhasil ditambahkan');
        } catch (\Exception $e) {
            if (isset($path) && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menambahkan data!' . $e->getMessage())
                ->withInput();
        }
    }

    public function detail($id)
    {
        $perda = Perda::findOrFail($id);
        return view('draft-phd.perda.detail', compact('perda'));
    }

    public function edit($id)
    {   
        $perda = Perda::findOrFail($id);
        return view('draft-phd.perda.edit', compact('perda'));
    }

    public function update(Request $request, $id)
    {
        try {
            $perda = Perda::findOrFail($id);
            $validated = $request->validate([
                'no_agenda' => 'required|string|max:255',
                'no_surat' => 'required|string|max:255',
                'pengirim' => 'required|string|max:255',
                'tanggal_surat' => 'required|date',
                'tanggal_terima' => 'required|date',
                'perihal' => 'required|string|max:255',
                'lampiran' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
            ]);

            if ($request->hasFile('lampiran')) {
                // Hapus file lama jika ada
                if ($perda->lampiran) {
                    Storage::disk('public')->delete($perda->lampiran);
                }

                $file = $request->file('lampiran');
                $path = $file->store('lampiran/perda', 'public');
                $validated['lampiran'] = $path;
            }
            
            $perda->update($validated);

            return redirect()->route('draft-phd.perda.index')
                ->with('success', 'Perda berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memperbarui data!');
        }
    }

    public function updateCatatan(Request $request, $id)
    {       
        try {
            $perda = Perda::findOrFail($id);
            $perda->update([
                'catatan' => $request->catatan
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Catatan berhasil diperbarui'
            ]);
                        
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui catatan'
            ], 500);
        }
    }

    public function status($id)
    {
        $perda = Perda::findOrFail($id);
        return view('draft-phd.perda.status', compact('perda'));
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:tercatat,terdisposisi,diproses,koreksi,diambil,selesai',
            ]);

            $perda = Perda::findOrFail($id);
            $perda->status = $request->status;
            $perda->save();

            return redirect()->back()->with('success', 'Status berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengupdate status: ' . $e->getMessage());
        }
    }

    public function destroy(Perda $perda)
    {
        if ($perda->lampiran) {
            Storage::disk('public')->delete($perda->lampiran);
        }

        $perda->delete();

        return redirect()->route('draft-phd.perda.index')
            ->with('success', 'Surat Peraturan Daerah berhasil dihapus');
    }

    public function export()
    {
        return Excel::download(new PerdaExport(), 'perda.xlsx');
    }

    public function disposisi(Request $request, $id)
    {
        try {
            $request->validate([
                'disposisi' => 'required',
                'sub_disposisi' => 'required_unless:disposisi,Kasubag Tata Usaha',
                'tanggal_disposisi' => 'required|date',
                'catatan' => 'nullable'
            ]);

            $perda = Perda::findOrFail($id);

            $disposisiText = $request->disposisi;
            if ($request->sub_disposisi) {
                $disposisiText .= ' | Diteruskan ke: ' . $request->sub_disposisi;
            }
            $disposisiText .= ' | Tanggal: ' . $request->tanggal_disposisi;
            if ($request->catatan) {
                $disposisiText .= ' | Catatan: ' . $request->catatan;
            }

            $perda->update([
                'disposisi' => $disposisiText
            ]);

            return redirect()->back()
                            ->with('success', 'Disposisi berhasil ditambahkan');

        } catch (\Exception $e) {
            return redirect()->back()
                            ->with('error', 'Gagal menambahkan disposisi: ' . $e->getMessage());
        }
    }

    public function updateDisposisi(Request $request, $id)
    {
        try {
            $request->validate([
                'disposisi' => 'required|string|max:255',
            ]);

            $perda = Perda::findOrFail($id);
            $perda->disposisi = $request->disposisi;
            $perda->save();

            return redirect()->back()
                            ->with('success', 'Disposisi berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()
                            ->with('error', 'Gagal mengupdate disposisi: ' . $e->getMessage());
        }
    }
}
