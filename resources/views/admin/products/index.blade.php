@extends('admin.layout')

@section('content')

<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default">
                <div class="card-header card-header-border-bottom">
                    <h2>Products</h2>
                </div>
                <div class="card-body">

                    @include('admin.partials.flash')

                    <div class="table-responsive-sm">
                        <table class="table table-bordered tabel-stripped">
                            <thead>
                                <th>#</th>
                                <th>SKU</th>
                                <th>Type</th>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th style="width: 10%">Action</th>
                            </thead>
                            <tbody>

                                @forelse ($products as $product)

                                <tr>
                                    <td>{{ $product->id }}</td>
                                    <td>{{ $product->sku }}</td>
                                    <td>{{ $product->type }}</td>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ number_format($product->price) }}</td>
                                    <td>{{ $product->statusLabel()}}</td>
                                    <td>
                                        <a href="{{ url('admin/products/'.$product->id.'/edit') }}"
                                            class="btn btn-warning btn-sm"><i class="mdi mdi-grease-pencil"></i></a>

                                        @can('delete_products')
                                        {!! Form::open(['url' => 'admin/products/'.$product->id, 'class' => 'delete',
                                        'style' => 'display:inline-block']) !!}
                                        {!! Form::hidden('_method', 'DELETE') !!}
                                        {!! Form::button('<i class="mdi mdi-close-outline"></i>', ['type' => 'submit',
                                        'class' => 'btn btn-danger btn-sm']) !!}
                                        {!! Form::close() !!}
                                        @endcan
                                    </td>
                                </tr>

                                @empty

                                <tr>
                                    <td colspan="7">No records found</td>
                                </tr>

                                @endforelse

                            </tbody>
                        </table>
                        {{ $products->links() }}
                    </div>
                </div>

                @can('add_products')
                <div class="card-footer text-right">
                    <a href="{{ url('admin/products/create') }}" class="btn btn-primary">Add New</a>
                </div>
                @endcan

            </div>
        </div>
        <div class="col-lg-12">
            <button></button>
        </div>
    </div>
</div>

@endsection

@section('unused')
@endsection
