<div class="flex items-center space-x-2">
    @role('admin|city-admin|vendor')
    <x-buttons.show :model="$model" />
    @if (!in_array($model->status, ['failed', 'delivered', 'cancelled']) && !in_array($model->payment_status, ['review']))
        <x-buttons.edit :model="$model" />
    @endif
@endrole
    @role('admin')
        @if (in_array($model->payment_status, ['review']))
            <x-buttons.review :model="$model" />
        @endif
    @endrole
</div>
