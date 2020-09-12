@extends('admin.layout')

@section('content')

<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default">
                <div class="card-header card-header-border-bottom">
                    <h2>Categories</h2>
                </div>
                <div class="card-body">

                    @include('admin.partials.flash')

                    <div class="table-responsive-sm">
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
                                    <td>{{ $category->parent_id ? $category->parent->name : ''}}</td>
                                    <td>
                                        @can('edit_categories')    
                                        <a href="{{ url('admin/categories/'.$category->id.'/edit') }}"
                                            {{-- class="btn btn-warning btn-sm">Edit</a> --}}
                                            class="btn btn-warning btn-sm"><i class="mdi mdi-grease-pencil"></i></a>
                                        @endcan
                                        @can('delete_categories')
                                        {!! Form::open(['url' => 'admin/categories/'.$category->id, 'class' => 'delete',
                                        'style' => 'display:inline-block']) !!}
                                        {!! Form::hidden('_method', 'DELETE') !!}
                                        {{-- {!! Form::submit('Remove', ['class' => 'btn btn-danger btn-sm']) !!} --}}
                                        {!! Form::button('<i class="mdi mdi-close-outline"></i>', ['type' => 'submit',
                                        'class' => 'btn btn-danger btn-sm']) !!}
                                        {!! Form::close() !!}
                                        @endcan
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
                @can('add_categories')
                <div class="card-footer text-right">
                    <a href="{{ url('admin/categories/create') }}" class="btn btn-primary">Add New</a>
                </div>
                @endcan
            </div>
        </div>
    </div>
</div>

@endsection

@section('responsive-table-div')
<div>
    @forelse ($categories as $category)

    <p>{{ $category->id }}</p>
    <p>{{ $category->name }}</p>
    <p>{{ $category->slug }}</p>
    <p>{{ $category->parent_id }}</p>
    <br>

    @empty

    <div>No records found</div>

    @endforelse
</div>
{{ $categories->links() }}
@endsection
