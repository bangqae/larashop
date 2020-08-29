<div class="card card-default">
    <div class="card-header card-header-border-bottom">
        <h2>Product Menus</h2>
    </div>
    <div class="card-body">
        <nav class="nav flex-column">
            <a href="{{ url('admin/products/'. $productID .'/edit') }}" class="nav-link">Product Detail</a>
            <a href="{{ url('admin/products/'. $productID .'/images') }}" class="nav-link">Product Images</a>
        </nav>
    </div>
</div>
