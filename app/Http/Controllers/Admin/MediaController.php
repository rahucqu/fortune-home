<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MediaRequest;
use App\Models\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class MediaController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->get('search', '');
        $type = $request->get('type', '');

        $media = Media::query()
            ->with('uploader:id,name')
            ->when($search, fn ($query) => $query->search($search))
            ->when($type, fn ($query) => $query->byType($type))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'total' => Media::count(),
            'images' => Media::images()->count(),
            'documents' => Media::byType('document')->count(),
            'videos' => Media::byType('video')->count(),
            'audio' => Media::byType('audio')->count(),
        ];

        return Inertia::render('Admin/Media/Index', [
            'media' => $media,
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'type' => $type,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Media/Create');
    }

    public function store(MediaRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Handle single file upload
        if ($request->hasFile('file')) {
            $this->handleFileUpload($request->file('file'), $validated);

            return redirect()->route('admin.media.index')
                ->with('success', 'Media file uploaded successfully.');
        }

        // Handle multiple file uploads
        if ($request->hasFile('files')) {
            $uploadedCount = 0;

            foreach ($request->file('files') as $file) {
                $this->handleFileUpload($file, $validated);
                $uploadedCount++;
            }

            return redirect()->route('admin.media.index')
                ->with('success', "Successfully uploaded {$uploadedCount} files.");
        }

        return redirect()->back()
            ->withErrors(['file' => 'No file was uploaded.']);
    }

    public function show(Media $media): Response
    {
        $media->load('uploader:id,name');

        return Inertia::render('Admin/Media/Show', [
            'media' => $media,
        ]);
    }

    public function edit(Media $media): Response
    {
        return Inertia::render('Admin/Media/Edit', [
            'media' => $media,
        ]);
    }

    public function update(MediaRequest $request, Media $media): RedirectResponse
    {
        $validated = $request->validated();

        // Remove file-related fields for update
        unset($validated['file'], $validated['files']);

        $media->update($validated);

        return redirect()->route('admin.media.index')
            ->with('success', 'Media updated successfully.');
    }

    public function destroy(Media $media): RedirectResponse
    {
        // Delete the physical file
        if ($media->path && Storage::disk('public')->exists($media->path)) {
            Storage::disk('public')->delete($media->path);
        }

        $media->delete();

        return redirect()->route('admin.media.index')
            ->with('success', 'Media deleted successfully.');
    }

    private function handleFileUpload(UploadedFile $file, array $validated): Media
    {
        // Generate unique filename
        $filename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $extension = $file->getClientOriginalExtension();
        $uniqueFilename = $filename . '-' . Str::random(6) . '.' . $extension;

        // Store file
        $path = $file->storeAs('media', $uniqueFilename, 'public');

        // Get file info
        $mimeType = $file->getMimeType();
        $size = $file->getSize();
        $type = Media::getTypeFromMimeType($mimeType);

        // Get image dimensions if it's an image
        $width = null;
        $height = null;
        if ($type === 'image') {
            $imagePath = $file->getPathname();
            $imageSize = getimagesize($imagePath);
            if ($imageSize) {
                $width = $imageSize[0];
                $height = $imageSize[1];
            }
        }

        // Create media record
        return Media::create([
            'name' => $validated['name'] ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'file_name' => $uniqueFilename,
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $mimeType,
            'type' => $type,
            'size' => $size,
            'width' => $width,
            'height' => $height,
            'alt_text' => $validated['alt_text'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'uploaded_by' => Auth::id(),
        ]);
    }
}
