<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use App\Models\Client;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Exception;

class ProposalController extends Controller
{
    protected GroqService $ai;

    public function __construct(GroqService $ai)
    {
        $this->ai = $ai;
    }

    /**
     * GET /api/proposals
     */
    public function index()
    {
        $proposals = Auth::user()
            ->proposals()
            ->with('client:id,name,email')
            ->latest()
            ->get();

        return response()->json($proposals);
    }

    /**
     * GET /api/proposals/{proposal}
     */
    public function show(Proposal $proposal)
    {
        $this->authorize('view', $proposal);

        return response()->json($proposal->load('client'));
    }

    /**
     * POST /api/proposals
     * Wizard Step 0: Proposal record create karo brief ke sath (status: draft)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'title' => 'required|string|max:255',
            'brief' => 'required|string|min:20',
        ]);

        // Client is user ka hi hona chahiye
        $client = Client::where('id', $validated['client_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $proposal = Proposal::create([
            'user_id' => Auth::id(),
            'client_id' => $client->id,
            'title' => $validated['title'],
            'brief' => $validated['brief'],
            'status' => 'draft',
            'generation_step' => 'brief',
        ]);

        return response()->json($proposal, 201);
    }

    /**
     * POST /api/proposals/{proposal}/generate-scope
     * Wizard Step 1
     */
    public function generateScope(Proposal $proposal)
    {
        $this->authorize('update', $proposal);

        try {
            $scope = $this->ai->generateScope($proposal->brief);

            $proposal->update([
                'scope' => $scope,
                'generation_step' => 'scope',
            ]);

            return response()->json($proposal->fresh());
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }
    }

    /**
     * POST /api/proposals/{proposal}/generate-timeline
     * Wizard Step 2
     */
    public function generateTimeline(Proposal $proposal)
    {
        $this->authorize('update', $proposal);

        if (!$proposal->scope) {
            return response()->json(['message' => 'Scope must be generated first.'], 422);
        }

        try {
            $timeline = $this->ai->generateTimeline($proposal->brief, $proposal->scope);

            $proposal->update([
                'timeline' => $timeline,
                'generation_step' => 'timeline',
            ]);

            return response()->json($proposal->fresh());
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }
    }

    /**
     * POST /api/proposals/{proposal}/generate-cost
     * Wizard Step 3
     */
    public function generateCost(Proposal $proposal)
    {
        $this->authorize('update', $proposal);

        if (!$proposal->scope || !$proposal->timeline) {
            return response()->json(['message' => 'Scope and timeline must be generated first.'], 422);
        }

        try {
            $cost = $this->ai->generateCostBreakdown(
                $proposal->brief,
                $proposal->scope,
                $proposal->timeline
            );

            $proposal->update([
                'cost_breakdown' => $cost,
                'total_amount' => $cost['total'] ?? 0,
                'generation_step' => 'cost',
            ]);

            return response()->json($proposal->fresh());
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }
    }

    /**
     * PUT /api/proposals/{proposal}
     * User AI output ko edit kare (scope/timeline/cost manually), ya finalize kare
     */
    public function update(Request $request, Proposal $proposal)
    {
        $this->authorize('update', $proposal);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'scope' => 'sometimes|array',
            'timeline' => 'sometimes|array',
            'cost_breakdown' => 'sometimes|array',
            'status' => 'sometimes|in:draft,sent,accepted,rejected',
            'generation_step' => 'sometimes|in:brief,scope,timeline,cost,completed',
        ]);

        // Agar user manually cost_breakdown edit kare, total_amount bhi sync kar do
        if (isset($validated['cost_breakdown']['total'])) {
            $validated['total_amount'] = $validated['cost_breakdown']['total'];
        }

        $proposal->update($validated);

        return response()->json($proposal->fresh());
    }

    /**
     * DELETE /api/proposals/{proposal}
     */
    public function destroy(Proposal $proposal)
    {
        $this->authorize('delete', $proposal);

        $proposal->delete();

        return response()->json(['message' => 'Proposal deleted.']);
    }
}