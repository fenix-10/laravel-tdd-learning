<h3>View page</h3>
<div>
    @foreach($posts as $post)
    <div>
        <h4>
            {{ $post->title }}
        </h4>
    </div>
    @endforeach
</div>
