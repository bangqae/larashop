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

                    <div class="table-responsive">
                        <table class="table table-bordered tabel-stripped">
                            <thead>
                                <th>#</th>
                                <th>SKU</th>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Action</th>
                            </thead>
                            <tbody>

                                @forelse ($products as $product)

                                <tr>
                                    <td>{{ $product->id }}</td>
                                    <td>{{ $product->sku }}</td>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->price }}</td>
                                    <td>{{ $product->status}}</td>
                                    <td>
                                        <a href="{{ url('admin/products/'.$product->id.'/edit') }}"
                                            class="btn btn-warning btn-sm"><i class="mdi mdi-grease-pencil"></i></a>

                                        {!! Form::open(['url' => 'admin/products/'.$product->id, 'class' => 'delete',
                                        'style' => 'display:inline-block']) !!}
                                        {!! Form::hidden('_method', 'DELETE') !!}
                                        {!! Form::button('<i class="mdi mdi-close-outline"></i>', ['type' => 'submit',
                                        'class' => 'btn btn-danger btn-sm']) !!}
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
                        {{ $products->links() }}
                    </div>
                </div>
                <div class="card-footer text-right">
                    <a href="{{ url('admin/products/create') }}" class="btn btn-primary">Add New</a>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <button></button>
        </div>
    </div>
</div>

@endsection

@section('unused')
<script type="text/javascript">
$('#button').click( function(){
$(".i")
});
</script>
@endsection
