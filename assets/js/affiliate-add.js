document.addEventListener('DOMContentLoaded', function () {
    handleAddCommissionSection();
    handleCommissionTypeChangeSection();
});

function handleAddCommissionSection() {
    const enableCommissionCheckbox = document.getElementById('enable_commission');
    const commissionSettingsDiv = document.getElementById('commission_settings');

    if (enableCommissionCheckbox && commissionSettingsDiv) {
        enableCommissionCheckbox.addEventListener('change', function () {
            commissionSettingsDiv.style.display = this.checked ? 'block' : 'none';
        });
        commissionSettingsDiv.style.display = enableCommissionCheckbox.checked ? 'block' : 'none';
    } else {
        console.warn('Element not found: enableCommissionCheckbox or commissionSettingsDiv is null.');
    }
}

function handleCommissionTypeChangeSection() {
    const commissionTypeSelect = document.getElementById('commission_type_select');
    const commissionByProduct = document.getElementById('commission_by_product');
    const commissionByOrder = document.getElementById('commission_by_order');
    const commissionByQuantity = document.getElementById('commission_by_quantity');

    if (commissionTypeSelect && commissionByProduct && commissionByOrder && commissionByQuantity) {
        commissionTypeSelect.addEventListener('change', function () {
            handleCommissionTypeChange(this.value);
        });
        handleCommissionTypeChange(commissionTypeSelect.value);
    } else {
        console.warn('Element not found: commissionTypeSelect or commission sections are null.');
    }

    function handleCommissionTypeChange(selectedType) {
        hideAndRemoveRequired(commissionByProduct);
        hideAndRemoveRequired(commissionByOrder);
        hideAndRemoveRequired(commissionByQuantity);

        if (selectedType === 'product') {
            showAndAddRequired(commissionByProduct);
        } else if (selectedType === 'order') {
            showAndAddRequired(commissionByOrder);
        } else if (selectedType === 'quantity') {
            showAndAddRequired(commissionByQuantity);
        }
    }

    function hideAndRemoveRequired(section) {
        section.style.display = 'none';
        section.querySelectorAll('input, select').forEach(input => {
            input.removeAttribute('required');
        });
    }

    function showAndAddRequired(section) {
        section.style.display = 'block';
        section.querySelectorAll('input, select').forEach(input => {
            input.setAttribute('required', 'required');
        });
    }
}
