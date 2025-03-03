<div class="fi-ta-text-item inline-flex items-center gap-1.5 px-3">
  @if (strstr($templates->type, "video/"))
  <video style="width:75px" src="{{ asset('storage/store/' . $templates->file) }}" controls></video>
  @else
    <img style="width:75px" src="{{ asset('storage/store/' . $templates->file) }}" alt="Gambar">  
  @endif
</div>