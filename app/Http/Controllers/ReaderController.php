<?php

namespace App\Http\Controllers;

use App\Archive;
use App\Http\Requests\Reader\PutReaderHistoryRequest;
use App\Manga;
use App\Image;
use App\ImageArchive;
use App\ReaderHistory;

class ReaderController extends Controller
{
    public function index(Manga $manga, Archive $archive, int $page)
    {
        $imgArchive = ImageArchive::open($manga->path . DIRECTORY_SEPARATOR . $archive->name);
        if ($imgArchive === false)
            return response()->make(null, 400);

        $images = $imgArchive->getImages();
        if ($images === false)
            return response()->make(null, 400);

        $pageCount = count($images);

        return view('manga.reader')
            ->with('manga', $manga)
            ->with('archive', $archive)
            ->with('page', $page)
            ->with('pageCount', $pageCount);
    }

    public function image(Manga $manga, Archive $archive, int $page)
    {
        return Image::response($manga, $archive, $page);
    }

    public function putReaderHistory(PutReaderHistoryRequest $request)
    {
        $manga = Manga::findOrFail($request->get('manga_id'))->load('archives');
        $archive = $manga->archives->find($request->get('archive_id'));

        $imgArchive = ImageArchive::open($manga->path . DIRECTORY_SEPARATOR . $archive->name);
        if ($imgArchive === false)
            return response()->make(null, 400);

        $images = $imgArchive->getImages();
        if ($images === false)
            return response()->make(null, 400);

        $pageCount = count($images);

        ReaderHistory::updateOrCreate([
            'user_id' => $request->user()->id,
            'manga_id' => $manga->id,
            'archive_id' => $archive->id,
            'page_count' => $pageCount,
        ], [
            'user_id' => $request->user()->id,
            'manga_id' => $manga->id,
            'archive_id' => $archive->id,
            'page' => $request->get('page'),
            'page_count' => $pageCount,
        ]);
    }
}
