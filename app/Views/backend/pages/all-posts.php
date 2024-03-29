<?= $this->extend('backend/layout/pages-layout'); ?>
<?= $this->Section('content') ?>
<div class="page-header">
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="title">
                <h4>All posts</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= route_to('admin.home') ?>">Home</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        All posts
                    </li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 col-sm-12 text-right">
            <div class="dropdown">
                <a class="btn btn-primary" href="<?= route_to('new-post') ?>">
                    Add new post
                </a>

            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card card-box">
            <div class="card-header">
                <div class="clear-fix">
                    <div class="pull-left">All posts</div>
                    <div class="pull-right"></div>
                </div>
            </div>
            <div class="card-body">
                <table class="data-table table stripe hover nowrap dataTable no-footer dtr-inline collapsed"
                    id="posts-table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Featured image</th>
                            <th scope="col">Title</th>
                            <th scope="col">Category</th>
                            <th scope="col">Visibility</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>






<?= $this->endSection() ?>

<?= $this->section('stylesheets') ?>
<link rel="stylesheet" href="/backend/src/plugins/datatables/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="/backend/src/plugins/datatables/css/responsive.bootstrap4.min.css">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="/backend/src/plugins/datatables/js/jquery.dataTables.min.js"></script>
<script src="/backend/src/plugins/datatables/js/dataTables.bootstrap4.min.js"></script>
<script src="/backend/src/plugins/datatables/js/dataTables.responsive.min.js"></script>
<script src="/backend/src/plugins/datatables/js/responsive.bootstrap4.min.js"></script>
<script>
// Retrieve Posts
var posts_DT = $('table#posts-table').DataTable({
    scrollCollapse: true,
    responsive: true,
    autoWidth: false,
    processing: true,
    serverSide: true,
    pageLength: 15,
    ajax: "<?= route_to('get-posts') ?>",
    "dom": "Brtip",
    info: true,
    fnCreatedRow: function(row, data, index) {
        $('td', row).eq(0).html(index + 1);
    },
    columnDefs: [{
        orderable: false,
        targets: 5
    }],
    order: [
        [1, 'asc']
    ]
});

$(document).on('click', '.deletePostBtn', function(e) {
    e.preventDefault();
    var post_id = $(this).data('id');
    var url = "<?= route_to('delete-post') ?>";
    Swal.fire({ // Swal must be a capitol / swal will throw a variable not found error
        title: 'Are you sure?',
        html: 'you want to delete this post',
        showCloseButton: true,
        showCancelButton: true,
        cancelButtonText: 'Cancel',
        confirmButtonText: 'Yes, Delete',
        cancelButtonColor: '#d33',
        confirmButtonColor: '#30853d',
        width: 400,
        allowOutsideClick: false
    }).then(function(result) {
        if (result.value) {
            $.getJSON(url, {
                post_id: post_id
            }, function(response) {
                if (response.status == 1) {
                    posts_DT.ajax.reload(null, false);
                    toastr.success(response.msg);
                } else {
                    toastr.error(response.msg);
                }
            });
        }
    });
});
</script>
<?= $this->endSection() ?>