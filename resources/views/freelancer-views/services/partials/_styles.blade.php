<style>
    .fsp-tier-card { border: 1px solid #e5e7eb; border-radius: 14px; background: #fff; height: 100%; transition: border-color .2s ease, box-shadow .2s ease; }
    .fsp-tier-card.is-enabled { border-color: #1a2f5e; box-shadow: 0 6px 18px rgba(26,47,94,.08); }
    .fsp-tier-header { padding: 14px 16px; border-bottom: 1px solid #eef1f5; display: flex; justify-content: space-between; align-items: center; }
    .fsp-tier-body { padding: 16px; display: flex; flex-direction: column; gap: 12px; }
    .fsp-tier-body.is-disabled { opacity: .45; pointer-events: none; }
    .service-image-row { border: 1px solid #e7ebf0; border-radius: 10px; padding: 12px; margin-bottom: 10px; }
    .service-image-row .service-image-drop {
        border: 2px dashed #d7dde5; border-radius: 8px; height: 90px; width: 100%;
        display: flex; align-items: center; justify-content: center; cursor: pointer;
        background: #fafbfc; overflow: hidden;
    }
    .service-image-row .service-image-drop img { max-width: 100%; max-height: 100%; object-fit: cover; display: none; }
    .service-image-row .service-image-drop i { font-size: 22px; color: #9aa5b1; }

    #freelancer-category-select, #freelancer-specialization-select {
        border-radius: 8px; border-color: #d7dde5;
    }
    #freelancer-category-select:focus, #freelancer-specialization-select:focus {
        border-color: #17395e; box-shadow: 0 0 0 3px rgba(23, 57, 94, 0.08);
    }
</style>
