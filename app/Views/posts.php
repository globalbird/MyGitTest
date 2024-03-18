<h1><?= $post->title; ?></h1>
<p><?= $post->content; ?></p>
<small>Posted on <?= date('F j, Y', strtotime($post->created_at)); ?></small>