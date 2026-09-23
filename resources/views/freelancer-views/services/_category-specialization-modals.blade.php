<style>
    .fsp-section-card { border: 1px solid #e5e7eb; border-radius: 14px; overflow: hidden; }
    .fsp-section-header {
        padding: 16px 20px; background: linear-gradient(90deg, #17395e 0%, #1f4a78 100%); color: #fff;
        display: flex; align-items: center; gap: 10px;
    }
    .fsp-section-header i { font-size: 18px; }
    .fsp-section-header h5 { margin: 0; font-weight: 800; font-size: 15px; color: #fff; }
    .fsp-section-header p { margin: 2px 0 0; font-size: 12px; color: rgba(255,255,255,.75); }
    .fsp-add-new-btn { white-space: nowrap; border-radius: 8px; font-weight: 700; font-size: 12px; }
</style>

<div class="modal fade" id="addFreelancerCategoryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ translate('add_your_own_category') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-0">
                    <label class="form-label">{{ translate('category_name') }} *</label>
                    <input type="text" class="form-control" id="new-freelancer-category-name" maxlength="100" required>
                    <div class="form-text">{{ translate('this_category_will_be_private_to_you_and_only_usable_on_your_own_services') }}</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('cancel') }}</button>
                <button type="button" class="btn btn--primary" id="save-new-freelancer-category-btn">{{ translate('save') }}</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addFreelancerSpecializationModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ translate('add_your_own_specialization') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">{{ translate('freelancer_category') }} *</label>
                    <select class="form-control" id="new-freelancer-specialization-category" required></select>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ translate('specialization_name') }} *</label>
                    <input type="text" class="form-control" id="new-freelancer-specialization-name" maxlength="100" required>
                </div>
                <div class="form-group mb-0">
                    <label class="form-label">{{ translate('description') }}</label>
                    <textarea class="form-control" id="new-freelancer-specialization-description" rows="2" maxlength="1000"></textarea>
                    <div class="form-text">{{ translate('this_specialization_will_be_private_to_you_and_only_usable_on_your_own_services') }}</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('cancel') }}</button>
                <button type="button" class="btn btn--primary" id="save-new-freelancer-specialization-btn">{{ translate('save') }}</button>
            </div>
        </div>
    </div>
</div>

@push('script')
    <script>
        (function () {
            const categorySelect = document.getElementById('freelancer-category-select');
            const specializationSelect = document.getElementById('freelancer-specialization-select');
            if (!categorySelect || !specializationSelect) return;

            const modalCategorySelect = document.getElementById('new-freelancer-specialization-category');
            const removeCategoryBtn = document.getElementById('remove-freelancer-category-btn');
            const removeSpecializationBtn = document.getElementById('remove-freelancer-specialization-btn');
            const categoryDestroyUrlTemplate = @json(route('freelancer.services.categories.destroy', ':id'));
            const specializationDestroyUrlTemplate = @json(route('freelancer.services.specializations.destroy', ':id'));

            function updateRemoveButtonState(select, button) {
                if (!button) return;
                const option = select.selectedOptions[0];
                button.classList.toggle('d-none', !(option && option.value && option.dataset.owner === '1'));
            }
            categorySelect.addEventListener('change', function () { updateRemoveButtonState(categorySelect, removeCategoryBtn); });
            specializationSelect.addEventListener('change', function () { updateRemoveButtonState(specializationSelect, removeSpecializationBtn); });
            updateRemoveButtonState(categorySelect, removeCategoryBtn);
            updateRemoveButtonState(specializationSelect, removeSpecializationBtn);

            removeCategoryBtn?.addEventListener('click', function () {
                const option = categorySelect.selectedOptions[0];
                if (!option || option.dataset.owner !== '1') return;
                if (!confirm('{{ translate('are_you_sure_you_want_to_remove_this_category') }}')) return;
                $.ajax({
                    url: categoryDestroyUrlTemplate.replace(':id', option.value),
                    method: 'DELETE',
                    data: { _token: @json(csrf_token()) },
                })
                    .done(function () {
                        option.remove();
                        categorySelect.value = '';
                        categorySelect.dispatchEvent(new Event('change'));
                        toastr.success('{{ translate('category_removed_successfully') }}');
                    })
                    .fail(function (xhr) {
                        toastr.error(xhr.responseJSON?.message || '{{ translate('something_went_wrong') }}');
                    });
            });

            removeSpecializationBtn?.addEventListener('click', function () {
                const option = specializationSelect.selectedOptions[0];
                if (!option || option.dataset.owner !== '1') return;
                if (!confirm('{{ translate('are_you_sure_you_want_to_remove_this_specialization') }}')) return;
                $.ajax({
                    url: specializationDestroyUrlTemplate.replace(':id', option.value),
                    method: 'DELETE',
                    data: { _token: @json(csrf_token()) },
                })
                    .done(function () {
                        option.remove();
                        specializationSelect.value = '';
                        specializationSelect.dispatchEvent(new Event('change'));
                        toastr.success('{{ translate('specialization_removed_successfully') }}');
                    })
                    .fail(function (xhr) {
                        toastr.error(xhr.responseJSON?.message || '{{ translate('something_went_wrong') }}');
                    });
            });

            function syncModalCategoryOptions() {
                modalCategorySelect.innerHTML = categorySelect.innerHTML;
                modalCategorySelect.value = categorySelect.value;
            }
            syncModalCategoryOptions();

            $('#addFreelancerSpecializationModal').on('show.bs.modal', syncModalCategoryOptions);

            document.getElementById('save-new-freelancer-category-btn')?.addEventListener('click', function () {
                const nameInput = document.getElementById('new-freelancer-category-name');
                const name = nameInput.value.trim();
                if (!name) {
                    nameInput.focus();
                    return;
                }
                $.post(@json(route('freelancer.services.categories.store')), { name: name, _token: @json(csrf_token()) })
                    .done(function (response) {
                        const option = document.createElement('option');
                        option.value = response.category.id;
                        option.textContent = response.category.name;
                        option.dataset.owner = '1';
                        categorySelect.appendChild(option);
                        categorySelect.value = response.category.id;
                        categorySelect.dispatchEvent(new Event('change'));
                        nameInput.value = '';
                        $('#addFreelancerCategoryModal').modal('hide');
                        toastr.success('{{ translate('category_added_successfully') }}');
                    })
                    .fail(function (xhr) {
                        toastr.error(xhr.responseJSON?.message || '{{ translate('something_went_wrong') }}');
                    });
            });

            document.getElementById('save-new-freelancer-specialization-btn')?.addEventListener('click', function () {
                const nameInput = document.getElementById('new-freelancer-specialization-name');
                const descriptionInput = document.getElementById('new-freelancer-specialization-description');
                const name = nameInput.value.trim();
                const categoryId = modalCategorySelect.value;
                if (!categoryId) {
                    toastr.error('{{ translate('please_select_a_freelancer_category_first') }}');
                    return;
                }
                if (!name) {
                    nameInput.focus();
                    return;
                }
                $.post(@json(route('freelancer.services.specializations.store')), {
                    name: name,
                    description: descriptionInput.value.trim(),
                    freelancer_category_id: categoryId,
                    _token: @json(csrf_token()),
                })
                    .done(function (response) {
                        const option = document.createElement('option');
                        option.value = response.specialization.id;
                        option.textContent = response.specialization.name;
                        option.dataset.category = response.specialization.freelancer_category_id;
                        option.dataset.owner = '1';
                        specializationSelect.appendChild(option);
                        categorySelect.value = response.specialization.freelancer_category_id;
                        categorySelect.dispatchEvent(new Event('change'));
                        specializationSelect.value = response.specialization.id;
                        nameInput.value = '';
                        descriptionInput.value = '';
                        $('#addFreelancerSpecializationModal').modal('hide');
                        toastr.success('{{ translate('specialization_added_successfully') }}');
                    })
                    .fail(function (xhr) {
                        toastr.error(xhr.responseJSON?.message || '{{ translate('something_went_wrong') }}');
                    });
            });
        })();
    </script>
@endpush
