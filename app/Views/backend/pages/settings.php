<?= $this->extend('backend/layout/pages-layout'); ?>
<?= $this->Section('content') ?>

<div class="page-header">
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <div class="title">
                <h4>Settings</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= route_to('admin.home') ?>">Home</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        Settings
                    </li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="pd-20 card-box mb-4">
    
    <div class="tab">
        <ul class="nav nav-tabs customtab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#general_settings" role="tab" aria-selected="true">General settings</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#logo_favicon" role="tab" aria-selected="false">Logo & Favicon</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#social_media" role="tab" aria-selected="false">Social media</a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="general_settings" role="tabpanel">
                <div class="pd-20">
                    <form action="<?= route_to('update-general-settings') ?>" method="POST" id="general_settings_form">
                        <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" class="ci_csrf_data">
                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Blog title</label>
                                    <input type="text" class="form-control" name="blog_title" placeholder="Enter Blog Title" value="<?= get_settings()->blog_title ?>">
                                    <span class="text-danger error-text blog_title_error"></span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Blog email</label>
                                    <input type="text" class="form-control" name="blog_email" placeholder="Enter blog email" value="<?= get_settings()->blog_email ?>">
                                    <span class="text-danger error-text blog_email_error"></span>
                                </div>
                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Blog phone no</label>
                                    <input type="text" class="form-control" name="blog_phone" placeholder="Enter blog phone" value="<?= get_settings()->blog_phone ?>">
                                    <span class="text-danger error-text blog_phone_error"></span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Blog meta keywords</label>
                                    <input type="text" class="form-control" name="blog_meta_keywords" placeholder="Enter blog meta keywords" 
                                    value="<?= get_settings()->blog_meta_keywords ?>">
                                    <span class="text-danger error-text blog_meta_keywords_error"></span>
                                </div>
                            </div>

                        </div>

                        <div class="form-group">
                            <label for="">Blog Address</label>
                            <textarea name="blog_address" id="blog_address" cols="4" rows="2" class="form-control" placeholder="Add your address"><?= get_settings()->blog_address ?></textarea>
                            <span class="text-danger error-text blog_address"></span>
                        </div>

                        <div class="form-group">
                            <label for="">Blog meta description</label>
                            <textarea name="blog_meta_description" id="" cols="4" rows="2" class="form-control" placeholder="Write blog meta description"><?= get_settings()->blog_meta_description ?></textarea>
                            <span class="text-danger error-text blog_meta_description_error"></span>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>

                    </form>
                </div>
            </div>
            <div class="tab-pane fade" id="logo_favicon" role="tabpanel">
                <div class="pd-20">
                <div class="row">
                    <div class="col-md-6">
                    <h5>Set blog logo</h5>
                    <div class="mb2 mt-1" style="max-width: 200px;">
                    <img src="" alt="" class="img-thumbnail" id="logo-image-preview" data-ijabo-default-img="/images/blog/<?= get_settings()->blog_logo ?>">
                    </div>
                    <form action="<?= route_to('update-blog-logo')?>" method="post" enctype="multipart/form-data" id="changeBlogLogoForm">
                        <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" class="ci_csrf_data">
                        <div class="mb-2">
                            <input type="file" name="blog_logo" id="" class="form-control">
                            <span class="text-danger error-text"></span>
                        </div>
                        <button type="submit" class="btn btn-primary">Change logo</button>
                    </form>
                    </div>
                    <div class="col-md-6">
                        <h5>Set blog favicon</h5>
                        <div class="mb2 mt-1" style="max: width 100px;">
                            <img src="" alt="" class="image-thumb" id="favicon-image-preview" data-ijabo-default-img="/images/blog/<?= get_settings()->blog_favicon ?>">
                        </div>
                        <form action="<?= route_to('update-blog-favicon')?>" method="post" enctype="multipart/form-data" id="changeBlogFaviconForm">
                            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" class="ci_csrf_data">
                            <div class="mb-2">
                                <input type="file" name="blog_favicon" id="" class="form-control">
                                <span class="text-danger error-text"></span>
                            </div>
                            <button type="submit" class="btn btn-primary">Change favicon</button>
                        </form>
                    </div>
                </div>
                </div>
            </div>
            <div class="tab-pane fade" id="social_media" role="tabpanel">
                <div class="pd-20">
                <form action="<?= route_to('update-social-media') ?>" method="post" id="social_media_form">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" class="ci_csrf_data">

                <div class="row">

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="">Facebook URL</label>
                            <input type="text" class="form-control" name="facebook_url" placeholder="Enter facebook page URL" value="<?= get_social_media()->facebook_url ?>" id="">
                            <span class="text-danger error-text facebook_url_error"></span>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="">Twitter URL</label>
                            <input type="text" class="form-control" name="twitter_url" placeholder="Enter Twitter URL" value="<?= get_social_media()->twitter_url ?>" id="">
                            <span class="text-danger error-text twitter_url_error"></span>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="">Instagram URL</label>
                            <input type="text" class="form-control" name="instagram_url" placeholder="Enter Instagram URL" value="<?= get_social_media()->instagram_url ?>" id="">
                            <span class="text-danger error-text instagram_url_error"></span>
                        </div>
                    </div>

                </div><!-- END ROW -->

                <div class="row">

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="">Youtube URL</label>
                            <input type="text" class="form-control" name="youtube_url" placeholder="Enter Youtube channel URL" value="<?= get_social_media()->youtube_url ?>" id="">
                            <span class="text-danger error-text youtube_url_error"></span>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="">LinkedIn URL</label>
                            <input type="text" class="form-control" name="linkedin_url" placeholder="Enter LinkedIn URL" value="<?= get_social_media()->linkedin_url ?>" id="">
                            <span class="text-danger error-text linkedin_url_error"></span>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="">GitHub URL</label>
                            <input type="text" class="form-control" name="github_url" placeholder="Enter GitHub URL" value="<?= get_social_media()->github_url ?>" id="">
                            <span class="text-danger error-text github_url_error"></span>
                        </div>
                    </div>

                </div><!-- END ROW -->

                <div class="form-group">
                <button type="submit" class="btn btn-primary">Save changes</button>
                </div>

                </form>

                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
    // Settings
    $('#general_settings_form').on('submit', function(e){
        e.preventDefault();
        //CSRF Hash
        var csrfName = $('.ci_csrf_data').attr('name'); // Token Name CSRF
        var csrfHash = $('.ci_csrf_data').val(); // CSRF Hash
        var form = this;
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
            beforeSend:function(){
                toastr.remove();
                $(form).find('span.error-text').text('');
            },
            success:function(response){
                // Update csrf hash
                $('.ci_csrf_data').val(response.token);

                if( $.isEmptyObject(response.error) ){
                    if( response.status == 1 ){
                        toastr.success(response.msg);
                    }else{
                        toastr.error(response.msg);
                    }
                }else{
                    $.each(response.error, function(prefix, val){
                        $(form).find('span.'+prefix+'_error').text(val);
                    })
                }

            }

        });
    });

    $('input[type="file"][name="blog_logo"]').ijaboViewer({
        preview:'#logo-image-preview',
        imageShape:'rectangular', // or square if is avatar
        allowedExtensions:['jpg','jpeg','png'],
        onErrorShape:function(message,element){
            alert(message);
        },
        onInvalidType:function(message,element){
            alert(message);
        },
        onSuccess:function(message,element){

        }

    });

    $('#changeBlogLogoForm').on('submit', function(e){
        e.preventDefault();
        var csrfName = $('.ci_csrf_data').attr('name'); // Token Name CSRF
        var csrfHash = $('.ci_csrf_data').val(); // CSRF Hash
        var form = this;
        var formdata = new FormData(form);
            formdata.append(csrfName,csrfHash);

        var inputFileVal = $(form).find('input[type="file"][name="blog_logo"]').val();

        if( inputFileVal.length > 0 ){
            
                    $.ajax({
                        url:$(form).attr('action'),
                        method:$(form).attr('method'),
                        data:formdata,
                        processData:false,
                        dataType:'json',
                        contentType:false,
                        beforeSend:function(){
                        toastr.remove();
                        $(form).find('span.error-text').text('');
                        },
                        success:function(response){
                            // Update CSRF Hash
                            $('.ci_csrf_data').val(response.token);

                            if( response.status == 1 ){
                                toastr.success(response.msg);
                                $(form)[0].reset();

                            }else{
                                toastr.error(response.msg);
                            }
                        }
                    });


        }else{
            $(form).find('span.error-text').text('Please, select logo image file.');
        }

        
    });

    $('input[type="file"][name="blog_favicon"]').ijaboViewer({
        preview:'#favicon-image-preview',
        imageShape:'square', // or square if is avatar
        allowedExtensions:['jpg','jpeg','png'],
        onErrorShape:function(message,element){
            alert(message);
        },
        onInvalidType:function(message,element){
            alert(message);
        },
        onSuccess:function(message,element){

        }
    });

    $('#changeBlogFaviconForm').on('submit', function(e){
        e.preventDefault();
        var csrfName = $('.ci_csrf_data').attr('name'); // Token Name CSRF
        var csrfHash = $('.ci_csrf_data').val(); // CSRF Hash
        var form = this;
        var formdata = new FormData(form);
            formdata.append(csrfName,csrfHash);

        var inputFileVal = $(form).find('input[type="file"][name="blog_favicon"]').val();

        if( inputFileVal.length > 0 ){
            
            $.ajax({
                url:$(form).attr('action'),
                method:$(form).attr('method'),
                data:formdata,
                processData:false,
                dataType:'json',
                contentType:false,
                beforeSend:function(){
                toastr.remove();
                $(form).find('span.error-text').text('');
                },
                success:function(response){
                    // Update CSRF Hash
                    $('.ci_csrf_data').val(response.token);

                    if( response.status == 1 ){
                        toastr.success(response.msg);
                        $(form)[0].reset();

                    }else{
                        toastr.error(response.msg);
                    }
                }
            });
        }else{
            $(form).find('span.error-text').text('Please, select favicon image file.');
            }
    });

    $('#social_media_form').on('submit', function(e) {
        e.preventDefault();
        var csrfName = $('.ci_csrf_data').attr('name'); // Token Name CSRF
        var csrfHash = $('.ci_csrf_data').val(); // CSRF Hash
        var form = this;
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
                beforeSend:function(){
                toastr.remove();
                $(form).find('span.error-text').text('');
                },
                success:function(response){
                    // Update CSRF Hash
                    $('.ci_csrf_data').val(response.token);

                    if( $.isEmptyObject(response.error) ){
                        if(response.status == 1){
                            toastr.success(response.msg);
                        }else{
                            toastr.success(response.msg);
                        }
                    }else{
                        $.each(response.error, function(prefix,val){
                            $(form).find('span.'+prefix+'_error').text(val);
                        });
                    }
                    
                    }
                });
        
    });
    
</script>
<?= $this->endSection() ?>