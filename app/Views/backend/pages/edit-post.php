<?= $this->extend('backend/layout/pages-layout'); ?>
<?= $this->Section('content') ;?>
<?php use App\Libraries\CIAuth; ?>
<div class="page-header">
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="title">
                <h4>Edit post</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= route_to('admin.home') ?>">Home</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        Edit post
                    </li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 col-sm-12 text-right">
            <a href="<?= route_to('all-posts') ?>" class="btn btn-primary">View all posts</a>
        </div>
    </div>
</div>

<form action="<?= route_to('update-post') ?>" method="post" autocomplete="off" enctype="multipart/form-data" id="updatePostForm">
    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" class="csrf_data">
    <input type="hidden" name="post_id" value="<?= $post->id ?>">
    <div class="row">
        <div class="col-md-9">
            <div class="card card-box mb-2">
                <div class="card-body">
                    <div class="form-group">
                        <label for=""><b>Post title</b></label>
                        <input type="text" name="title" id="" class="form-control" placeholder="Enter post title"
                            value="<?= $post->title ?>">
                        <span class="text-danger error-text title_error"></span>
                    </div>
                    <div class="form-group">
                        <label for=""><b>Content</b></label>
                        <textarea id="content" name="content" cols="30" rows="10" class="form-control"
                            placeholder="Add your content ..."
                            aria-describedby="content_help"><?= $post->content ?></textarea>
                        <div class="form-text" id="content_help"><small class="text-muted">Content must have 20 or more
                                characters</small></div>
                        <span class="text-danger error-text content_error"></span>

                    </div>
                </div>
            </div>
            <div class="card card-box mb-2">
                <h5 class="card-header weight-500">SEO</h5>
                <div class="card-body">
                    <div class="form-group">
                        <label for=""><b>Meta keywords</b><small>&nbsp;(Separated by comma)</small></label>
                        <input type="text" name="meta_keywords" class="form-control" placeholder="Enter meta keywords"
                            value="<?= $post->meta_keywords ?>">
                    </div>
                    <div class="form-group">
                        <label for=""><b>Meta Description</b></label>
                        <textarea name="meta_description" id="" cols="30" rows="10" class="form-control"
                            placeholder="Enter meta description"><?= $post->meta_description ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-box mb-2">
                <div class="card-body">
                    <div class="form-group">
                        <label for=""><b>Post category</b></label>
                        <select name="category" id="" class="custom-select form-control">
                            <?php foreach($categories as $category): ?>
                            <option value="<?= $category->id ?>"
                                <?= $category->id == $post->category_id ? 'selected' : '' ?>><?= $category->name ?>
                            </option>
                            <?php endforeach   ?>
                        </select>
                        <span class="text-danger error-text category_error"></span>
                    </div>
                    <div class="form-group">
                        <label for=""><b>Featured Image</b></label>
                        <input type="file" name="featured-image" id="" class="form-control-file form-control"
                            height="auto" aria-describedby="featured_image_help">
                        <div class="form-text txt-sm" id="featured_image_help"><small class="text-muted">Use rectangular
                                images no larger than 2MB</small></div>
                        <span class="text-danger error-text featured-image_error"></span>
                    </div>
                    <div class="d-block mb-3" style="max-width:250px;">
                        <img src="" alt="" class="image-thumb" id="image-previewer"
                            data-ijabo-default-img="/images/posts/resized_<?= $post->featured_image ?>">
                    </div>
                    <div class="form-group">
                        <label for=""><b>Tags</b></label>
                        <input type="text" name="tags" class="form-control" placeholder="Enter tags"
                            data-role="tagsinput" value="<?= $post->tags ?>">
                        <span class="text-danger error-text tags_error"></span>
                    </div>
                    <hr>
                    <div class="form-group">
                        <label for=""><b>Visibility</b></label>
                        <div class="custom-control custom-radio mb-5">
                            <input type="radio" name="visibility" id="customRadio1" class="custom-control-input"
                                value="1" <?= $post->visibility == 1 ? 'checked' : '' ?>>
                            <label for="customRadio1" class="custom-control-label">Public</label>
                        </div>
                        <div class="custom-control custom-radio mb-5">
                            <input type="radio" name="visibility" id="customRadio2" class="custom-control-input"
                                value="0" <?= $post->visibility == 0 ? 'checked' : '' ?>>
                            <label for="customRadio2" class="custom-control-label">Draft</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="mb-3">
        <button type="submit" class="form-control">Save changes</button>
    </div>
</form>

<?= $this->endSection() ?>

<?= $this->section('stylesheets') ?>

<link rel="stylesheet" href="/backend/src/plugins/bootstrap-tagsinput/bootstrap-tagsinput.css">

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>

<script src="/backend/src/plugins/bootstrap-tagsinput/bootstrap-tagsinput.js"></script>

<script src="/extra-assets/ckeditor/ckeditor.js"></script>

<script>
$(function(){
        var elfinderPath = 
        '/extra-assets/elFinder/elfinder.src.php?integration=ckeditor&uid=<?= CIAuth::id() ?>';
        
        CKEDITOR.replace('content',{
            filebrowserBrowseUrl:elfinderPath,
            filebrowserImageBrowseUrl:elfinderPath+'&type=image',
            removeDialogTabs:'link:upload;image:upload'
        });
    });    
$('input[type="file"][name="featured-image"]').ijaboViewer({
    preview: '#image-previewer',
    imageShape: 'rectangular',
    allowedExtensions: ['jpg', 'png', 'jpeg'],
    onErrorShape: function(message, element) {
        alert(message);
    },
    oninvalidType: function(message, element) {
        alert(message);
    }
});

$('#updatePostForm').on('submit', function(e) {
    e.preventDefault();
    //alert('POST CREATED...SUBMIT');
    var csrfName = $('.csrf_data').attr('name'); // token
    var csrfHash = $('.csrf_data').val(); // hash
    var form = this;
    var content = CKEDITOR.instances.content.getData();
    var formdata = new FormData(form);
        formdata.append(csrfName, csrfHash);
        formdata.append('content',content);

    $.ajax({
        url: $(form).attr('action'),
        method: $(form).attr('method'),
        data: formdata,
        processData: false,
        dataType: 'json',
        contentType: false,
        beforeSend: function() {
            toastr.remove();
            $(form).find('span.error-text').text('');
        },
        success: function(response) {
            // Update CSRF Hash
            $('.ci_csrf_data').val(response.token);

            if ($.isEmptyObject(response.error)) {
                if (response.status == 1) {                    
                    toastr.success(response.msg);
                    $(document).ajaxStop(function() {
                        window.location.href="<?= route_to('all-posts') ?>";
                    });
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
</script>

<?= $this->endSection() ?>