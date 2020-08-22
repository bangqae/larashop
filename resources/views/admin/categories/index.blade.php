@extends('admin.layout')

@section('content')

<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="card cart-default">
                <div class="card-header card-header-border-bottom">
                    <h2>Categories</h2>
                </div>
                <div class="card-body">

                    @include('admin.partials.flash')

                    <div class="table-responsive">
                        <table class="table table-bordered tabel-stripped">
                            <thead>
                                <th>#</th>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Parent</th>
                                <th>Action</th>
                            </thead>
                            <tbody>

                                @forelse ($categories as $category)
                                <tr>
                                    <td>{{ $category->id }}</td>
                                    <td>{{ $category->name }}</td>
                                    <td>{{ $category->slug }}</td>
                                    <td>{{ $category->parent_id }}</td>
                                    <td>
                                        <a href="{{ url('admin/categories/'.$category->id.'/edit') }}"
                                            class="btn btn-warning btn-sm">Edit</a>
                                        {{-- <a href="{{ url('admin/categories/'.$category->id.'/edit') }}"
                                            class="btn btn-warning btn-sm"><i class="mdi mdi-grease-pencil"></i></a> --}}

                                        {!! Form::open(['url' => 'admin/categories/'.$category->id, 'class' => 'delete', 'style' => 'display:inline-block']) !!}
                                        {!! Form::hidden('_method', 'DELETE') !!}
                                        {!! Form::submit('Remove', ['class' => 'btn btn-danger btn-sm']) !!}
                                        {!! Form::close() !!}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5">No records found</td>
                                </tr>
                                @endforelse

                            </tbody>
                        </table>
                        {{ $categories->links() }}
                    </div>
                </div>
                <div class="card-footer text-right">
                    <a href="{{ url('admin/categories/create') }}" class="btn btn-primary">Add New</a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
