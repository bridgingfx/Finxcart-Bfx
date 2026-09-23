@php
    $item = $item ?? null;
@endphp
<style>
    .portfolio-form-card { border-radius: 14px; overflow: hidden; }
    .portfolio-image-drop {
        border: 2px dashed #d7dde5; border-radius: 12px; padding: 24px; text-align: center;
        cursor: pointer; transition: border-color .15s ease; background: #fafbfc;
    }
    .portfolio-image-drop:hover { border-color: #1684cf; }
    .portfolio-image-drop img { max-width: 100%; max-height: 220px; border-radius: 8px;{{ $item ? '' : ' display: none;' }} }
    .portfolio-image-drop .drop-placeholder i { font-size: 34px; color: #9aa5b1; }
    .tag-input-wrapper {
        display: flex; flex-wrap: wrap; gap: 6px; align-items: center;
        border: 1px solid #d7dde5; border-radius: 8px; padding: 6px 8px; min-height: 44px;
    }
    .tag-input-wrapper input {
        border: none; outline: none; flex: 1; min-width: 120px; padding: 4px;
    }
    .tag-chip {
        display: inline-flex; align-items: center; gap: 6px; background: #eef3f8; color: #1c2b3a;
        border-radius: 999px; padding: 4px 10px; font-size: 13px; font-weight: 500;
    }
    .tag-chip .remove-tag { cursor: pointer; color: #6c757d; font-weight: 700; }
    .tag-chip .remove-tag:hover { color: #dc3545; }
    .gallery-row { border: 1px solid #e7ebf0; border-radius: 10px; padding: 12px; margin-bottom: 10px; }
    .gallery-row .gallery-thumb-drop {
        border: 2px dashed #d7dde5; border-radius: 8px; height: 90px; width: 100%;
        display: flex; align-items: center; justify-content: center; cursor: pointer;
        background: #fafbfc; overflow: hidden;
    }
    .gallery-row .gallery-thumb-drop img { max-width: 100%; max-height: 100%; object-fit: cover; display: none; }
    .gallery-row .gallery-thumb-drop i { font-size: 22px; color: #9aa5b1; }
</style>
