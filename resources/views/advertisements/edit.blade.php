@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Edit Advertisement</h2>

    <form action="{{ url('/advertisements/' . $ad->id . '/update') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Description</label>
            <textarea name="advertisement_description" class="form-control" required>
                {{ $ad->advertisement_description }}
            </textarea>
        </div>

        <div class="mb-3">
            <label>Category</label>
            <select name="category_id" class="form-control">
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ $ad->category_id == $cat->id ? 'selected' : '' }}>
                        {{ $cat->category_name_en }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>District</label>
            <select name="district_id" class="form-control">
                @foreach($districts as $d)
                    <option value="{{ $d->id }}" {{ $ad->district_id == $d->id ? 'selected' : '' }}>
                        {{ $d->district_name_en }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>City</label>
            <select name="city_id" class="form-control">
                @foreach($cities as $c)
                    <option value="{{ $c->id }}" {{ $ad->city_id == $c->id ? 'selected' : '' }}>
                        {{ $c->city_name_en }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Publish Date</label>
            <input type="date" name="publish_date" class="form-control"
                   value="{{ $ad->publish_date }}">
        </div>

        <div class="mb-3">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="1" {{ $ad->status == 1 ? 'selected' : '' }}>Active</option>
                <option value="0" {{ $ad->status == 0 ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <button class="btn btn-primary">Update</button>
        <a href="{{ url('/advertisements') }}" class="btn btn-secondary">Cancel</a>

    </form>
</div>
@endsection