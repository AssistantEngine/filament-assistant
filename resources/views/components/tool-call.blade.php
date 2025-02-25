@php
    // Safely decode the arguments; fall back to an empty array if decoding fails
    $args = [];
    if (!empty($message['function']['arguments'])) {
        $decoded = json_decode($message['function']['arguments'], true);
        $args = is_array($decoded) ? $decoded : [];
    }
@endphp

<div class="flex items-start mb-4">
    <!-- A distinct color for tool call messages -->
    <div class="bg-green-100 dark:bg-green-900 text-green-900 dark:text-green-100 p-3 rounded-lg shadow-sm w-full">
        <!-- Headline: slightly lighter text than the body -->
        <div class="{{empty($args) ? '': 'mb-2'}} font-semibold text-xs">
            Tool: {{ $message['function']['name'] ?? 'N/A' }}
        </div>

        @if ($args)
            <p class="text-sm font-medium mb-1">Arguments:</p>
            <ul class="list-disc list-inside text-sm pl-4 space-y-1">
                @foreach($args as $key => $value)
                    <li>
                        <span class="font-semibold">{{ $key }}:</span>
                        @if(is_array($value))
                            {{ json_encode($value) }}
                        @else
                            {{ $value }}
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
