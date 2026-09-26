<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Releases\CreateRelease;
use App\Http\Requests\StoreReleaseRequest;
use App\Models\Release;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class ReleaseController extends Controller
{
    public function index(): Response
    {
        $releases = Release::query()
            ->published()
            ->latest()
            ->paginate(10);

        return Inertia::render('Releases/Index', [
            'releases' => $releases,
        ]);
    }

    public function show(string $slug): Response
    {
        $release = Release::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return Inertia::render('Releases/Show', [
            'release' => $release,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Releases/Create');
    }

    public function store(StoreReleaseRequest $request, CreateRelease $createRelease): RedirectResponse
    {
        $createRelease($request->validated());

        return redirect()
            ->route('releases.index')
            ->with('success', 'Release created.');
    }
}
