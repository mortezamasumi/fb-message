<div class="flex flex-col gap-2">
    <div class="">
        {{ $getLabel() }}
    </div>
    <div class="flex gap-6">
        @foreach ($getRecord()->getMedia() as $media)
            <a class="flex flex-col w-16 h-24 gap-2 rounded" href="{{ $media->getUrl() }}" target="_blank">
                @switch($media->mime_type)
                    @case('application/pdf')
                        <img src={{ Storage::url('icons/pdf.png') }} class="w-16 h-16 rounded">
                    @break

                    @case('image/raw')
                    @case('image/jpeg')

                    @case('image/jpg')
                    @case('image/bmp')

                    @case('image/gif')
                    @case('image/webp')

                    @case('image/png')
                        <img src={{ $media->getUrl('thumb') }} class="w-16 h-16 rounded">
                    @break

                    @case('video/mp4')
                    @case('video/quicktime')

                    @case('video/webm')
                    @case('video/wmv')

                    @case('video/flv')
                    @case('video/avi')

                    @case('video/ogg')
                    @case('video/3gp')
                        <img src={{ Storage::url('icons/video.png') }} class="w-16 h-16 rounded">
                    @break

                    @case('audio/m4a')
                    @case('audio/ogg')

                    @case('audio/mpeg')
                    @case('audio/aac')

                    @case('audio/mp3')
                    @case('audio/wav')

                    @case('audio/x-wav')
                        <img src={{ Storage::url('icons/audio.png') }} class="w-16 h-16 rounded">
                    @break

                    @case('image/tiff')
                        <img src={{ Storage::url('icons/tiff.png') }} class="w-16 h-16 rounded">
                    @break

                    @default
                        <img src={{ Storage::url('icons/unknown.png') }} class="w-16 h-16 rounded">
                @endswitch
                <span class="text-[0.6rem] break-words">{{ str($media->name)->limit(10) }}</span>
                {{-- <span class="text-xs">{{ $media->mime_type }}</span> --}}
            </a>
        @endforeach
    </div>
</div>
