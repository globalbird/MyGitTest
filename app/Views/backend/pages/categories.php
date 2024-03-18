<?= $this->extend('backend/layout/pages-layout'); ?>
<?= $this->Section('content') ?>

<div class="page-header">
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <div class="title">
                <h4>Categories</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= route_to('admin.home') ?>">Home</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        Categories
                    </li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="row">

    <div class="col-md-12 mb-4">
        <div class="card card-box">
            <div class="card-header">
                <div class="clearfix">
                    <div class="pull-left">
                        Categories
                    </div>
                    <div class="pull-right">
                        <a href="" class="btn btn-default btn-sm p-0" role="button" id="add_category_btn">
                            <i class="fa fa-plus-circle"></i> Add category
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless table-hover table-stripe" id="categories-table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Category name</th>
                            <th scope="col">N. of sub categories</th>
                            <th scope="col">Action</th>
                            <th scope="col">Ordering</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-12 mb-4">
        <div class="card card-box">
            <div class="card-header">
                <div class="clearfix">
                    <div class="pull-left">
                        Sub categories
                    </div>
                    <div class="pull-right">
                        <a href="" class="btn btn-default btn-sm p-0" role="button" id="add_subcategory_btn">
                            <i class="fa fa-plus-circle"></i> Add sub category
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless table-hover table-stripe" id="sub-categories-table">
                    <thead>
                        <th scope="col">#</th>
                        <th scope="col">Sub category name</th>
                        <th scope="col">Parent category</th>
                        <th scope="col">N. of post(s)</th>
                        <th scope="col">Action</th>
                        <th scope="col">Ordering</th>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php include('modals/category-modals-form.php') ?>
<?php include('modals/edit-category-modals-form.php') ?>
<?php include('modals/subcategory-modals-form.php') ?>
<?php include('modals/edit-subcategory-modals-form.php') ?>

<?= $this->endSection() ?>

<?= $this->section('stylesheets')?>

<link rel="stylesheet" type="text/css" href="/backend/src/plugins/datatables/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="/backend/src/plugins/datatables/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="/backend/src/plugins/sweetalert2/sweetalert2.css">
<link rel="stylesheet" type="text/css" href="/extra-assets/jquery-ui-1.13.2/jquery-ui.min.css">
<link rel="stylesheet" type="text/css" href="/extra-assets/jquery-ui-1.13.2/jquery-ui.structure.min.css">
<link rel="stylesheet" type="text/css" href="/extra-assets/jquery-ui-1.13.2/jquery-ui.theme.min.css">

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>

<script src="/backend/src/plugins/datatables/js/jquery.dataTables.min.js"></script>
<script src="/backend/src/plugins/datatables/js/dataTables.bootstrap4.min.js"></script>
<script src="/backend/src/plugins/sweetalert2/sweetalert2.all.js"></script>
<script src="/backend/src/plugins/sweetalert2/sweet-alert.init.js"></script>
<script src="/backend/src/plugins/datatables/js/dataTables.responsive.min.js"></script>
<script src="/backend/src/plugins/datatables/js/responsive.bootstrap4.min.js"></script>
<script src="/extra-assets/jquery-ui-1.13.2/jquery-ui.min.js"></script>



<script>
$(document).on('click', '#add_category_btn', function(e) {
    e.preventDefault();
    var modal = $('body').find('div#category-modal');
    var modal_title = 'Add category';
    var modal_btn_text = 'ADD';
    modal.find('.modal-title').html(modal_title);
    modal.find('.modal-footer > button.action').html(modal_btn_text);
    modal.find('input.error-text').html('');
    modal.find('input[type="text"]').val('');
    modal.modal('show');
});

$('#add_category_form').on('submit', function(e) {
    e.preventDefault();
    // CSRF Hash
    var csrfName = $('.ci_csrf_data').attr('name'); // CSRF Token name
    var csrfHash = $('.ci_csrf_data').val(); // CSRF Hash
    var form = this;
    var modal = $('body').find('div#category-modal');
    var formdata = new FormData(form);
    formdata.append(csrfName, csrfHash);

    $.ajax({
        url: $(form).attr('action'),
        method: $(form).attr('method'),
        data: formdata,
        processData: false,
        dataType: 'json',
        contentType: false,
        cache: false,
        beforeSend: function() {
            toastr.remove();
            $(form).find('span.error-text').text('');
        },
        success: function(response) {
            // Update CSRF Hash
            $('.ci_csrf_data').val(response.token);

            if ($.isEmptyObject(response.error)) {
                if (response.status == 1) {
                    $(form)[0].reset();
                    modal.modal('hide');
                    toastr.success(response.msg);
                    categories_DT.ajax.reload(null, false); // Update DataTable
                    subcategoriesDT.ajax.reload(null, false);
                } else {
                    toastr.error(response.msg);
                }
            } else {
                $.each(response.error, function(prefix, val) {
                    $(form).find('span.' + prefix + '_error').text(val);
                });
            }
        }

    });

});

// Retrieve categories
var categories_DT = $('#categories-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: "<?= route_to('get-categories') ?>",
    dom: "Brtip",
    info: true,
    fnCreatedRow: function(row, data, index) {
        $('td', row).eq(0).html(index + 1);
        // console.log(data);
        $('td', row).parent().attr('data-index', data[0]).attr('data-ordering', data[4]);
    },
    columnDefs: [{
            orderable: false,
            targets: [0, 1, 2, 3]
        },
        {
            visible: false,
            targets: 4
        }
    ],
    order: [
        [4, 'asc']
    ]
});

$(document).on('click', '.editCategoryBtn', function(e) {
    e.preventDefault();
    var category_id = $(this).data('id');
    // alert(category_id); / Get the category id that is clicked / var category_id
    var url = "<?= route_to('get-category') ?>";
    $.get(url, {
        category_id: category_id
    }, function(response) {
        var modal_title = 'Edit category';
        var modal_btn_text = 'Save changes';
        var modal = $('body').find('div#edit-category-modal');
        modal.find('form').find('input[type="hidden"][name="category_id"]').val(category_id);
        modal.find('.modal-title').html(modal_title);
        modal.find('.modal-footer > button.action').html(modal_btn_text);
        modal.find('input[type="text"]').val(response.data.name);
        modal.find('span.error-text').html('');
        modal.modal('show');
    }, 'json');

});

$('#update_category_form').on('submit', function(e) {
    e.preventDefault();
    //CSRF
    var csrfName = $('.ci_csrf_data').attr('name'); //CSRF token
    var csrfHash = $('.ci_csrf_data').val(); //CSRF hash

    var form = this;
    var modal = $('body').find('div#edit-category-modal');
    var formdata = new FormData(form);
    formdata.append(csrfName, csrfHash);

    $.ajax({
        url: $(form).attr('action'),
        method: $(form).attr('method'),
        data: formdata,
        processData: false,
        dataType: 'json',
        contentType: false,
        cache: false,
        beforeSend: function() {
            toastr.remove();
            $(form).find('span.error-text').text('');
        },
        success: function(response) {
            // Update CSRF Hash
            $('.ci_csrf_data').val(response.token);

            if ($.isEmptyObject(response.error)) {
                if (response.status == 1) {
                    modal.modal('hide');
                    toastr.success(response.msg);
                    categories_DT.ajax.reload(null, false);
                    subcategoriesDT.ajax.reload(null, false);
                } else {
                    toastr.error(response.msg);
                }
            } else {
                $.each(response.error, function(prefix, val) {
                    $(form).find('span.' + prefix + '_error').text(val);
                });
            }
        }
    });


});

$(document).on('click', '.deleteCategoryBtn', function(e) {
    e.preventDefault();
    var category_id = $(this).data('id');
    var url = '<?= route_to('delete-category') ?>';

    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonClass: 'btn btn-primary w-xs me-2 mt-2',
        cancelButtonClass: 'btn btn-danger w-xs mt-2',
        confirmButtonText: "Yes, delete it!",
        buttonsStyling: false,
        showCloseButton: true
    }).then(function(result) {
        if (result.value) {
            $.get(url, {
                category_id: category_id
            }, function(response) {
                if (response.status == 1) {
                    categories_DT.ajax.reload(null, false);
                    subcategoriesDT.ajax.reload(null, false);
                    toastr.success(response.msg);
                } else {
                    toastr.error(response.msg);
                }
            }, 'json');
        }
    });


});


$('table#categories-table').find('tbody').sortable({
    update: function(event, ui) {
        $(this).children().each(function(index) {
            if ($(this).attr('data-ordering') != (index + 1)) {
                $(this).attr('data-ordering', (index + 1)).addClass('updated');
            }
        });

        var positions = [];
        $('.updated').each(function() {
            positions.push([$(this).attr('data-index'), $(this).attr('data-ordering')]);
            $(this).removeClass('updated');
        });

        var url = "<?= route_to('reorder-categories') ?>";
        $.get(url, {
            positions: positions
        }, function(response) {
            if (response.status == 1) {
                categories_DT.ajax.reload(null, false);
                toastr.success(response.msg);
            }

        }, 'json');
    }
});

$(document).on('click', '#add_subcategory_btn', function(e) {
    e.preventDefault();

    var modal_title = 'Add Sub Category';
    var modal_btn_text = 'ADD';
    var modal = $('body').find('div#sub-category-modal');
    var select = modal.find('select[name="parent_cat"]');
    var url = "<?= route_to('get-parent-categories') ?>";

    $.getJSON(url, {
        parent_category_id: null
    }, function(response) {
        select.find('option').remove();
        select.html(response.data)
    });
    modal.find('.modal-title').html(modal_title);
    modal.find('.modal-footer > button.action').html(modal_btn_text);
    modal.find('input[type="text"]').val('');
    modal.find('textarea').html('');
    modal.find('span.error-text').html('');
    modal.modal('show');

});

$(document).on('submit', '#add_subcategory_form', function(e) {
    e.preventDefault();

    var csrfName = $('.ci_csrf_data').attr('name'); // CSRF Token Name
    var csrfHash = $('.ci_csrf_data').val(); // CSRF Hash

    var form = this;
    var modal = $('body').find('div#sub-category-modal');
    var formdata = new FormData(form);
    formdata.append(csrfName, csrfHash);

    $.ajax({
        url: $(form).attr('action'),
        method: $(form).attr('method'),
        data: formdata,
        processData: false,
        dataType: 'json',
        contentType: false,
        cache: false,
        beforeSend: function() {
            toastr.remove();
            $(form).find('span.error-text').text('');
        },
        success: function(response) {
            // Update CSRF Hash
            $('.ci_csrf_data').val(response.token);

            if ($.isEmptyObject(response.error)) {
                if (response.status == 1) {
                    $(form)[0].reset();
                    modal.modal('hide');
                    toastr.success(response.msg);
                    subcategoriesDT.ajax.reload(null, false);
                    categories_DT.ajax.reload(null, false);
                }
            } else {
                $.each(response.error, function(prefix, val) {
                    $(form).find('span.' + prefix + '_error').text(val);
                });
            }
        }
    });
});

// Retrieve Sub categories   Check:: https://youtu.be/AbCu1ABMGz8?t=250

var subcategoriesDT = $('#sub-categories-table').DataTable({
    processing:true,
    serverSide:true,
    ajax:"<?= route_to('get-subcategories') ?>",
    dom:"Brtip",
    info:true,
    fnCreatedRow:function(row, data, index) 
    {
        $('td',row).eq(0).html(index+1);
        $('td',row).parent().attr('data-index',data[0]).attr('data-ordering',data[5]);
    },
    columnDefs:[
        { orderable: false, targets:[0, 1, 2, 3, 4] },
        { visible: false, targets: 5 },
    ],
    order:[[5, 'asc']]
});

$(document).on('click','.editSubCategoryBtn', function(e){
    e.preventDefault();
    var subcategory_id = $(this).data('id');
    // alert(subcategory_id);
    var get_subcategory_url = "<?= route_to('get-subcategory') ?>";
    var get_parent_categories_url = "<?= route_to('get-parent-categories') ?>";
    var modal_title = 'Edit subcategory';
    var modal_btn_text = 'Save changes';
    var modal = $('body').find('div#edit-sub-category-modal');
    modal.find('.modal-title').html(modal_title);
    modal.find('.modal-footer > button.action').html(modal_btn_text);
    modal.find('span.error-text').html('');
    var select = modal.find('select[name="parent_cat"]');

    $.getJSON(get_subcategory_url,{ subcategory_id:subcategory_id }, function(response){
        modal.find('input[type="text"][name="subcategory_name"]').val(response.data.name);
        modal.find('form').find('input[type="hidden"][name="subcategory_id"]').val(response.data.id);
        modal.find('form').find('textarea[name="description"]').val(response.data.description);

        $.getJSON(get_parent_categories_url,{ parent_category_id:response.data.parent_cat }, function(response){
            select.find('option').remove();
            select.html(response.data);
        });
        modal.modal('show');
    });

});

$('#update_subcategory_form').on('submit', function(e){
    e.preventDefault();

    var csrfName = $('.ci_csrf_data').attr('name'); // CSRF Token
    var csrfHash = $('.ci_csrf_data').val(); // CSRF Hash
    var form = this;
    var modal = $('body').find('div#edit-sub-category-modal');
    var formdata = new FormData(form);
        formdata.append(csrfName,csrfHash);

    $.ajax({
        url:$(form).attr('action'),
        method:$(form).attr('method'),
        data:formdata,
        processData:false,
        dataType:'json',
        contentType:false,
        cache:false,
        beforeSend:function()
        {
            toastr.remove();
            $(form).find('span.error-text').text('');
        },
        success:function(response)
        {
            $('.ci_csrf_data').val(response.token);

            if( $.isEmptyObject(response.error) )
            {
                if(response.status == 1){
                    modal.modal('hide');
                    toastr.success(response.msg);
                    subcategoriesDT.ajax.reload(null,false);
                }else
                {
                    toastr.error(response.msg);
                }
            }else
            {
                $.each(response.error, function(prefix, val){ 
                    $(form).find('span.'+prefix+'_error').text(val);
                });
            }
        }
    });    

}); 

// Reorder subcategories
$('table#sub-categories-table').find('tbody').sortable({
    update:function(event, ui){
        $(this).children().each(function(index)
        {
            if( $(this).attr('data-ordering') != (index + 1) ){
                $(this).attr('data-ordering',(index + 1)).addClass('updated');
            }
        });
        var positions = [];
        $('.updated').each(function(){
            positions.push([$(this).attr('data-index'),$(this).attr('data-ordering')]);
            $(this).removeClass('updated');
        });
        var url = "<?= route_to('reorder-subcategories') ?>";
        $.getJSON(url,{ positions:positions }, function(response){
            if(response.status == 1){
                subcategoriesDT.ajax.reload(null,false);
                toastr.success(response.msg);
            }
        });
    }
});

// delete SubCategory
$(document).on('click', '.deleteSubCategoryBtn', function(e) {
    e.preventDefault();
    var subcategory_id = $(this).data('id');
    var url = '<?= route_to('delete-subcategory') ?>';

    Swal.fire({
        title: "Are you sure?",
        html: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonClass: 'btn btn-primary w-xs me-2 mt-2',
        cancelButtonClass: 'btn btn-danger w-xs mt-2',
        confirmButtonText: "Yes, delete",
        buttonsStyling: false,
        showCloseButton: true
    }).then(function(result) {
        if (result.value) {
            $.getJSON(url, {
                subcategory_id: subcategory_id
            }, function(response) {
                if (response.status == 1) {                    
                    subcategoriesDT.ajax.reload(null, false);
                    toastr.success(response.msg);
                } else {
                    toastr.error(response.msg);
                }
            }, 'json');
        }
    });


});
</script>
<?= $this->endSection() ?>