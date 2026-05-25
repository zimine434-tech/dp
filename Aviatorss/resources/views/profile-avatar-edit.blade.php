@extends($user->role === 'teacher' ? 'layouts.teacher' : 'layouts.student')

@section('title', 'Изменить фотографию')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/cropperjs@1.6.2/dist/cropper.min.css">
@endpush

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="rounded-lg bg-white p-6 shadow-md">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h1 class="text-2xl font-bold text-gray-900">Изменить фотографию</h1>
                <a href="{{ route('profile') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                    Назад в профиль
                </a>
            </div>

            @error('avatar')
                <div class="mb-4 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ $message }}
                </div>
            @enderror

            <form id="avatar-form" method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="hidden" name="avatar_cropped" id="avatar_cropped">

                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <input
                        id="avatar-input"
                        type="file"
                        name="avatar"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        class="block w-full text-sm text-gray-700 file:mr-3 file:rounded-md file:border-0 file:bg-blue-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-200 sm:w-auto"
                        required
                    >
                    <button type="submit" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">
                        Сохранить фотографию
                    </button>
                </div>
                @if (request()->boolean('upload_err'))
                    <p class="text-sm text-red-600 pt-2" role="alert">Превышен размер файла.</p>
                @endif
                <div class="overflow-hidden rounded-lg border border-gray-200 bg-gray-50 p-3">
                    <img id="avatar-preview" src="{{ $user->avatar_url ?? '' }}" alt="Предпросмотр" class="max-h-[28rem] w-full object-contain {{ $user->avatar_url ? '' : 'hidden' }}">
                    <div id="avatar-empty" class="{{ $user->avatar_url ? 'hidden' : '' }} flex h-72 items-center justify-center text-sm text-gray-500">
                        Загрузите свою фотографию
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/cropperjs@1.6.2/dist/cropper.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var input = document.getElementById('avatar-input');
            var image = document.getElementById('avatar-preview');
            var empty = document.getElementById('avatar-empty');
            var form = document.getElementById('avatar-form');
            var croppedField = document.getElementById('avatar_cropped');
            var cropper = null;

            var initCropper = function () {
                if (cropper) cropper.destroy();
                cropper = new Cropper(image, {
                    aspectRatio: 1,
                    viewMode: 1,
                    autoCropArea: 1,
                    dragMode: 'move',
                    background: false,
                    responsive: true
                });
            };

            input.addEventListener('change', function (event) {
                var file = event.target.files && event.target.files[0];
                if (!file) return;
                var reader = new FileReader();
                reader.onload = function (e) {
                    image.src = e.target.result;
                    image.classList.remove('hidden');
                    empty.classList.add('hidden');
                    image.onload = initCropper;
                };
                reader.readAsDataURL(file);
            });

            form.addEventListener('submit', function (event) {
                if (!cropper) return;
                event.preventDefault();
                var canvas = cropper.getCroppedCanvas({
                    width: 512,
                    height: 512,
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high'
                });
                croppedField.value = canvas.toDataURL('image/png');
                form.submit();
            });
        });
    </script>
@endpush
