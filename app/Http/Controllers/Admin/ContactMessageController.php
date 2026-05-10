<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;

class ContactMessageController extends Controller
{
    // ── Helper: transform satu Eloquent ContactMessage → array ──────────────
    private function transformMessage(ContactMessage $m): array
    {
        return [
            'id'                   => $m->id,
            'nama'                 => $m->nama,
            'email'                => $m->email,
            'nomor_telepon'        => $m->nomor_telepon,
            'pesan'                => $m->pesan,
            'created_at'           => $m->created_at,
            'updated_at'           => $m->updated_at,
            'created_at_formatted' => Carbon::parse($m->created_at)->translatedFormat('d F Y, H:i'),
            'created_at_short'     => Carbon::parse($m->created_at)->format('d/m/Y'),
            'created_at_time'      => Carbon::parse($m->created_at)->format('H:i'),
            'pesan_preview'        => \Str::limit($m->pesan, 80, '...'),
            'time_ago'             => Carbon::parse($m->created_at)->diffForHumans(),
        ];
    }

    // ── index ────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = ContactMessage::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('pesan', 'LIKE', "%{$search}%")
                  ->orWhere('nomor_telepon', 'LIKE', "%{$search}%");
            });
        }

        // Filter date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        // Filter bulan
        if ($request->filled('bulan')) {
            $query->whereMonth('created_at', $request->get('bulan'));
        }

        // Filter tahun
        if ($request->filled('tahun')) {
            $query->whereYear('created_at', $request->get('tahun'));
        }

        $paginator = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $contactMessages = [
            'data'         => $paginator->getCollection()->map(fn ($m) => $this->transformMessage($m))->all(),
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
            'per_page'     => $paginator->perPage(),
            'total'        => $paginator->total(),
            'links'        => $paginator->linkCollection()->toArray(),
        ];

        // List bulan unik untuk filter dropdown
        $bulanList = ContactMessage::selectRaw('MONTH(created_at) as bulan')
            ->distinct()
            ->whereNotNull('created_at')
            ->orderBy('bulan')
            ->get()
            ->map(fn ($item) => [
                'value' => $item->bulan,
                'label' => Carbon::create()->month($item->bulan)->translatedFormat('F'),
            ]);

        // List tahun unik untuk filter dropdown
        $tahunList = ContactMessage::selectRaw('YEAR(created_at) as tahun')
            ->distinct()
            ->whereNotNull('created_at')
            ->orderBy('tahun', 'desc')
            ->get()
            ->map(fn ($item) => [
                'value' => $item->tahun,
                'label' => $item->tahun,
            ]);

        // Statistik
        $stats = [
            'total_messages'      => ContactMessage::count(),
            'today_messages'      => ContactMessage::whereDate('created_at', today())->count(),
            'this_week_messages'  => ContactMessage::whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
            ])->count(),
            'this_month_messages' => ContactMessage::whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count(),
        ];

        return Inertia::render('admin/contact-messages/Index', [
            'contactMessages' => $contactMessages,
            'filters'         => $request->only(['search', 'date_from', 'date_to', 'bulan', 'tahun']),
            'bulanList'       => $bulanList,
            'tahunList'       => $tahunList,
            'stats'           => $stats,
        ]);
    }

    // ── show ─────────────────────────────────────────────────────────────────
    public function show(string $id): Response
    {
        $message = ContactMessage::findOrFail($id);

        // Navigasi pesan sebelum & sesudah
        $previousMessage = ContactMessage::where('id', '<', $id)
            ->orderBy('id', 'desc')
            ->first();

        $nextMessage = ContactMessage::where('id', '>', $id)
            ->orderBy('id', 'asc')
            ->first();

        return Inertia::render('admin/contact-messages/Show', [
            'contactMessage'  => $this->transformMessage($message),
            'previousMessage' => $previousMessage ? [
                'id'                   => $previousMessage->id,
                'nama'                 => $previousMessage->nama,
                'created_at_formatted' => Carbon::parse($previousMessage->created_at)->translatedFormat('d F Y'),
            ] : null,
            'nextMessage'     => $nextMessage ? [
                'id'                   => $nextMessage->id,
                'nama'                 => $nextMessage->nama,
                'created_at_formatted' => Carbon::parse($nextMessage->created_at)->translatedFormat('d F Y'),
            ] : null,
        ]);
    }

    // ── destroy ──────────────────────────────────────────────────────────────
    public function destroy(string $id): RedirectResponse
    {
        $message = ContactMessage::findOrFail($id);

        try {
            $message->delete();

            return redirect()->route('admin.contact-messages.index')
                ->with('success', 'deleted');

        } catch (\Exception $e) {
            \Log::error('Error deleting contact message: ' . $e->getMessage());

            return back()->withErrors([
                'delete_error' => "Terjadi kesalahan saat menghapus pesan dari {$message->nama}. Silakan coba lagi atau hubungi administrator.",
            ])->with('error', 'Terjadi kesalahan saat menghapus pesan.');
        }
    }
}
