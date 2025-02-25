<div class="flex items-start mb-4">
    <!-- Right-aligned “bubble” style for the assistant message -->
    <div class="text-gray-800 dark:text-gray-200 p-3 pl-0 max-w-xl">
        @if(isset($message['name']))<div class="font-semibold mb-1">{{$message['name'] ?? ''}}</div>@endif
        <div>
            {!! \Illuminate\Support\Str::markdown($message['content']) !!}
        </div>
    </div>
</div>
