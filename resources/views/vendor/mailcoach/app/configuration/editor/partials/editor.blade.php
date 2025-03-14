<div>
    <p class="markup-links">
        {!! __mc('<a href=":link">Editor.js</a> is a beautiful block based <abbr title="What You See Is What You Get">WYSIWYG</abbr> editor. It also offers image uploads.', ['link' => 'https://editorjs.io']) !!}
    </p>

    <div class="mt-6">
        <x-mailcoach::alert type="info">
            {{ __mc('Editor.js stores content in a structured way. When switching from or to Editor.js, content in existing templates and draft campaigns will be lost.') }}
        </x-mailcoach::alert>
    </div>
</div>
