<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Bundling Promo - Grosir ZhamZham</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white py-3">
                    <h5 class="mb-0">✏️ Strategi Manajemen - Edit Paket Bundling Promo</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('bundling.update', $bundling->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">Kode Paket</label>
                                <input type="text" value="{{ $bundling->bundle_code }}" class="form-control bg-light" disabled>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">Nama Paket Promo</label>
                                <input type="text" name="bundle_name" class="form-control" value="{{ old('bundle_name', $bundling->name) }}" placeholder="Contoh: Paket Berkah Ramadan" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">Harga Paket Baru (Rp)</label>
                                <input type="number" name="bundle_price" class="form-control" value="{{ old('bundle_price', $bundling->bundle_price) }}" placeholder="Masukkan harga bundling" required>
                            </div>
                        </div>

                        <h6 class="mt-4 mb-3 text-primary fw-bold">Komponen Item Sembako Yang Digabungkan:</h6>
                        
                        <table class="table table-bordered align-middle" id="table-bundling">
                            <thead class="table-light">
                                <tr>
                                    <th>Pilih Barang Sembako</th>
                                    <th width="150">Kuantitas (Qty)</th>
                                    <th width="100" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bundling->details as $detail)
                                    <tr>
                                        <td>
                                            <select name="product_id[]" class="form-select" required>
                                                <option value="">-- Pilih Produk --</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}" {{ $detail->product_id == $product->id ? 'selected' : '' }}>
                                                        {{ $product->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="qty[]" class="form-control" min="1" value="{{ $detail->qty }}" required>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-row">Hapus</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        
                        <button type="button" class="btn btn-outline-primary btn-sm mb-4" id="add-row">+ Tambah Produk Lain</button>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('bundling.index') }}" class="btn btn-secondary px-4">
                                Kembali
                            </a>
                            <button type="submit" class="btn btn-primary px-4">Simpan Perubahan Paket</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $("#add-row").click(function () {
        let newRow = `<tr>
            <td>
                <select name="product_id[]" class="form-select" required>
                    <option value="">-- Pilih Produk --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" name="qty[]" class="form-control" min="1" value="1" required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm remove-row">Hapus</button>
            </td>
        </tr>`;
        $("#table-bundling tbody").append(newRow);
    });

    $(document).on('click', '.remove-row', function () {
        if ($("#table-bundling tbody tr").length > 1) {
            $(this).closest('tr').remove();
        } else {
            alert('Paket bundling minimal harus memiliki 1 produk penyusun!');
        }
    });
</script>
</body>
</html>