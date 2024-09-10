<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Models\Tag;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\TagResource;
use App\Http\Resources\V1\TagCollection;
use Illuminate\Support\Arr;
use App\Http\Requests\V1\BulkStoreTagRequest;
use App\Http\Requests\V1\UpdateTagRequest;

class TagController extends Controller
{
    /**
     *  Display a listing of the resource
     * 
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new TagCollection(Tag::all());
    }

     /**
     *  Display a one thing of resource
     * 
     * @param \App\Models\Tag $tag
     * @return \Illuminate\Http\Response
     */
    public function show(Tag $tag)
    {
        return  new TagResource($tag);
    }

    /**
     *  Store a newly created resource in storage
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function bulkStore(BulkStoreTagRequest $request)
    { 
        $bulk = collect($request->all())->map(function($arr, $key) {
            return Arr::except($arr, ['tagName']);
        });

        Tag::insert($bulk->toArray());
    }
}
