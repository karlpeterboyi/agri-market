<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJournalEntryRequest;
use App\Http\Resources\JournalEntryResource;
use App\Models\Farm;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JournalEntryController extends Controller
{
    public function index(Request $request)
    {
        $query = JournalEntry::with(['lines.account', 'farm', 'creator']);

        if (!in_array(auth()->user()->role ?? '', ['admin'])) {
            $query->whereHas('farm', fn ($q) => $q->where('owner_id', auth()->id()));
        }

        if ($request->filled('farm_id')) {
            $query->where('farm_id', $request->farm_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('entry_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('entry_date', '<=', $request->to_date);
        }

        return JournalEntryResource::collection(
            $query->latest('entry_date')->paginate(30)
        );
    }

    public function store(StoreJournalEntryRequest $request)
    {
        $data = $request->validated();

        // Validate double-entry balance
        $totalDebit = collect($data['lines'])->sum(fn ($l) => (float) ($l['debit'] ?? 0));
        $totalCredit = collect($data['lines'])->sum(fn ($l) => (float) ($l['credit'] ?? 0));

        if (round($totalDebit, 2) !== round($totalCredit, 2)) {
            return response()->json([
                'message' => 'Journal entry is not balanced. Total debits must equal total credits.',
                'debits' => $totalDebit,
                'credits' => $totalCredit,
            ], 422);
        }

        if (!empty($data['farm_id'])) {
            Farm::where('id', $data['farm_id'])
                ->where('owner_id', auth()->id())
                ->firstOrFail();
        }

        $entry = DB::transaction(function () use ($data) {
            $entry = JournalEntry::create([
                'farm_id' => $data['farm_id'] ?? null,
                'reference' => 'JE-' . strtoupper(Str::random(10)),
                'entry_date' => $data['entry_date'],
                'source' => $data['source'],
                'description' => $data['description'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($data['lines'] as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $line['account_id'],
                    'debit' => $line['debit'] ?? 0,
                    'credit' => $line['credit'] ?? 0,
                ]);
            }

            return $entry;
        });

        return (new JournalEntryResource(
            $entry->load(['lines.account', 'farm', 'creator'])
        ))->response()->setStatusCode(201);
    }

    public function show(JournalEntry $journalEntry)
    {
        $this->authorizeAccess($journalEntry);

        return new JournalEntryResource(
            $journalEntry->load(['lines.account', 'farm', 'creator'])
        );
    }

    public function destroy(JournalEntry $journalEntry)
    {
        $this->authorizeAccess($journalEntry);

        $journalEntry->delete(); // lines cascade

        return response()->json(['message' => 'Journal entry deleted.']);
    }

    protected function authorizeAccess(JournalEntry $entry): void
    {
        if (in_array(auth()->user()->role ?? '', ['admin'])) {
            return;
        }

        if ($entry->farm && $entry->farm->owner_id !== auth()->id()) {
            abort(403);
        }
    }
}
