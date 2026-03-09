{{-- Activity / Change log --}}
<div class="bg-white border border-gray-200 rounded-lg shadow-sm">
    <div class="p-5 border-b border-gray-200">
        <h2 class="text-base font-semibold text-gray-900">Activity log</h2>
        <p class="text-sm text-gray-500 mt-1">Recent changes to this product — who did what and when.</p>
    </div>

    @if($recentLogs->isNotEmpty())
    <div class="divide-y divide-gray-100">
        @foreach($recentLogs as $log)
        <div class="px-5 py-3 hover:bg-gray-50">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-sm text-gray-900">
                        @php
                            $actionLabels = [
                                'created' => 'Created product',
                                'updated' => 'Updated product',
                                'deleted' => 'Deleted product',
                                'duplicated' => 'Duplicated product',
                                'variants_generated' => 'Generated variants',
                                'variant_updated' => 'Updated variant',
                                'variant_deleted' => 'Deleted variant',
                                'variants_bulk_activated' => 'Bulk activated variants',
                                'variants_bulk_deactivated' => 'Bulk deactivated variants',
                                'variants_bulk_price' => 'Bulk updated variant prices',
                                'images_uploaded' => 'Uploaded images',
                                'images_reordered' => 'Reordered images',
                                'image_deleted' => 'Deleted image',
                                'login' => 'Logged in',
                                'logout' => 'Logged out',
                            ];
                        @endphp
                        <span class="font-medium">{{ $actionLabels[$log->action] ?? ucfirst(str_replace('_', ' ', $log->action)) }}</span>
                    </p>

                    @if($log->changes)
                    <div class="mt-1 space-y-0.5">
                        @foreach($log->changes as $field => $change)
                            @if(is_array($change) && isset($change['old'], $change['new']))
                            <p class="text-xs text-gray-500">
                                <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $field)) }}:</span>
                                <span class="text-red-600 line-through">{{ is_string($change['old']) ? \Illuminate\Support\Str::limit($change['old'], 60) : json_encode($change['old']) }}</span>
                                →
                                <span class="text-green-700">{{ is_string($change['new']) ? \Illuminate\Support\Str::limit($change['new'], 60) : json_encode($change['new']) }}</span>
                            </p>
                            @elseif(is_scalar($change))
                            <p class="text-xs text-gray-500">
                                <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $field)) }}:</span>
                                {{ $change }}
                            </p>
                            @endif
                        @endforeach
                    </div>
                    @endif

                    <p class="text-xs text-gray-400 mt-1">
                        by {{ $log->user->name ?? 'System' }}
                        @if($log->ip_address) · {{ $log->ip_address }} @endif
                    </p>
                </div>

                <time class="text-xs text-gray-400 whitespace-nowrap flex-shrink-0" title="{{ $log->created_at }}">
                    {{ $log->created_at->diffForHumans() }}
                </time>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="p-8 text-center">
        <p class="text-gray-500 text-sm">No activity recorded yet.</p>
    </div>
    @endif
</div>

