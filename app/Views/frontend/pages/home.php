<?= $this->extend('frontend/layout/pages-layout') ?>
<?= $this->section('page_meta') ?>
<meta name="robots" content="index,follow"/>
<meta name="title" content="<?= get_settings()->blog_title ?>"/>
<meta name="description" content="<?= get_settings()->blog_meta_description ?>"/>  
<meta name="author" content="<?= get_settings()->blog_title  ?>"/>
<link rel="canonical" href="<?= base_url() ?>">
<meta property="og:title" content="<?= get_settings()->blog_title  ?>">
<meta property="og:type" content="website"/>
<meta property="og:description" content="<?= get_settings()->blog_meta_description ?>"/>
<meta property="og:url" content="<?= base_url() ?>"/>
<meta property="og:image" content="<?= base_url('images/blog'.get_settings()->blog_logo) ?>"/>
<meta name="twitter:domain" content="<?= base_url() ?>"/>
<meta name="twitter:card" content="summary"/>
<meta name="twitter:title" property="og:title" itemprop="name" content="<?= get_settings()->blog_title  ?>"/>
<meta name="twitter:description" property="og:description" itemprop="description" content="<?= get_settings()->blog_meta_description ?>"/>
<meta name="twitter:image" content="<?= base_url('images/blog/'.get_settings()->blog_logo) ?>"/>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="row no-gutters-lg">
        <div class="col-12">
          <h2 class="section-title">Latest Articles</h2>
        </div>
        <div class="col-lg-8 mb-5 mb-lg-0">
          <div class="row">
            <div class="col-12 mb-4">
              <article class="card article-card">
                <a href="<?= route_to('read-post',get_home_main_latest_post()->slug) ?>">
                  <div class="card-image">
                    <div class="post-info"> <span class="text-uppercase">
                        <?= date_formatter(get_home_main_latest_post()->created_at) ?>
                    </span>
                      <span class="text-uppercase"><?= get_reading_time(get_home_main_latest_post()->content) ?></span>
                    </div>
                    <img loading="lazy" decoding="async" src="images/posts/<?= get_home_main_latest_post()->featured_image ?>" alt="Post Thumbnail" class="w-100">
                  </div>
                </a>
                <div class="card-body px-0 pb-1">
                 
                  <h2 class="h1"><a class="post-title" href="<?= route_to('read-post',get_home_main_latest_post()->slug) ?>"><?= get_home_main_latest_post()->title ?></a></h2>
                  <p class="card-text"><?= limit_words(get_home_main_latest_post()->content,35) ?></p>
                  <div class="content"> <a class="read-more-btn" href="<?= route_to('read-post',get_home_main_latest_post()->slug) ?>">Read Full Article</a>
                  </div>
                </div>
              </article>
            </div>
            <?php if(count(get_6_home_latest_posts()) > 0): ?>
                <?php foreach(get_6_home_latest_posts() as $post) : ?>
            <div class="col-md-6 mb-4">
              <article class="card article-card article-card-sm h-100">
                <a href="<?= route_to('read-post',$post->slug) ?>">
                  <div class="card-image">
                    <div class="post-info"> <span class="text-uppercase"><?= date_formatter($post->created_at) ?></span>
                      <span class="text-uppercase"><?= get_reading_time($post->content) ?></span>
                    </div>
                    <img loading="lazy" decoding="async" src="images/posts/resized_<?= $post->featured_image ?>" alt="Post Thumbnail" class="w-100 mx-cat-post-image">
                  </div>
                </a>
                <div class="card-body px-0 pb-0">
                  <h2><a class="post-title" href="<?= route_to('read-post',$post->slug) ?>">
                  <?= $post->title ?>
                  </a></h2>
                  <p class="card-text"><?= limit_words($post->content,13) ?></p>
                  <div class="content"> <a class="read-more-btn" href="<?= route_to('read-post',$post->slug) ?>">Read Full Article</a>
                  </div>
                </div>
              </article>
            </div>
                <?php endforeach ?>
            <?php endif; ?>
            
          </div>
        </div>
        <div class="col-lg-4">
  <div class="widget-blocks">
    <div class="row">
      
      <div class="col-lg-12 col-md-6">
        <div class="widget">
          <h2 class="section-title mb-3">What you may have missed</h2>
          <div class="widget-body">
            <div class="widget-list">
               <?php if(count(get_sidebar_random_posts()) >= 3) : ?>
                <?php foreach(get_sidebar_random_posts() as $post) : ?>
               <a class="media align-items-center" href="<?= route_to('read-post',$post->slug) ?>">
                <img loading="lazy" decoding="async" src="/images/posts/resized_<?= $post->featured_image ?>" alt="Post Thumbnail" class="w-100 ">
                <div class="media-body ml-3">
                  <h5><?= $post->title ?></h5>
                  <p class="mb-0 small"><?php echo limit_words($post->content,6) ?></p>
                </div>
               </a>
               <?php endforeach ?>
               <?php endif; ?>    
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-12 col-md-6">
        <?php include('partials/sidebar-categories.php') ?>
      </div>
    </div>
  </div>
</div>
      </div>
<?= $this->endSection() ?>