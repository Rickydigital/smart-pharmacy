@push('scripts')
<script>
    $(function () {
        const allCategoryOptions = [];

        $('.js-category-select:first option, .js-category-filter:first option').each(function () {
            const $option = $(this);

            allCategoryOptions.push({
                value: $option.attr('value') || '',
                text: $option.text(),
                typeId: $option.data('type-id') ? String($option.data('type-id')) : '',
            });
        });

        function rebuildCategorySelect($categorySelect, typeId, selectedValue = null) {
            if (!$categorySelect.length) {
                return;
            }

            const oldValue = selectedValue !== null ? String(selectedValue) : String($categorySelect.val() || '');

            $categorySelect.empty();

            allCategoryOptions.forEach(function (option) {
                if (option.value === '') {
                    $categorySelect.append(new Option(option.text, option.value, false, oldValue === ''));
                    return;
                }

                if (!typeId || option.typeId === String(typeId)) {
                    const selected = oldValue === String(option.value);
                    $categorySelect.append(new Option(option.text, option.value, selected, selected));
                }
            });

            if ($categorySelect.find('option[value="' + oldValue + '"]').length === 0) {
                $categorySelect.val('');
            } else {
                $categorySelect.val(oldValue);
            }

            $categorySelect.trigger('change.select2');
        }

        function initSelect2Clear() {
            $('.select2-clear').each(function () {
                const $select = $(this);

                if ($select.hasClass('select2-hidden-accessible')) {
                    return;
                }

                $select.select2({
                    width: '100%',
                    allowClear: true,
                    placeholder: $select.data('placeholder') || 'Select option'
                });
            });
        }

        function initSelect2Modal($modal) {
            $modal.find('.select2-modal').each(function () {
                const $select = $(this);

                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                }

                $select.select2({
                    width: '100%',
                    allowClear: true,
                    dropdownParent: $modal,
                    placeholder: $select.data('placeholder') || 'Select option'
                });
            });

            $modal.find('.js-type-select').each(function () {
                const $typeSelect = $(this);
                const $categorySelect = $modal.find('.js-category-select').first();

                if ($categorySelect.length) {
                    rebuildCategorySelect($categorySelect, $typeSelect.val(), $categorySelect.val());
                }
            });
        }

        initSelect2Clear();

        $('.js-type-filter').on('change', function () {
            rebuildCategorySelect($('.js-category-filter'), $(this).val(), '');
        });

        $('.modal').on('shown.bs.modal', function () {
            initSelect2Modal($(this));
        });

        $(document).on('change', '.js-type-select', function () {
            const $modal = $(this).closest('.modal');
            rebuildCategorySelect($modal.find('.js-category-select').first(), $(this).val(), '');
        });

        rebuildCategorySelect(
            $('.js-category-filter'),
            $('.js-type-filter').val(),
            '{{ request('product_category_id') }}'
        );
    });
</script>
@endpush