// services.js - Service Calculator and PayPal Integration

let paypalButtonsInstance = null;
let currentPrice = 0;
let currentServiceType = '';

document.addEventListener('DOMContentLoaded', function() {
    initServiceCalculator();
    initDatePicker();
    initFormValidation();
});

function initServiceCalculator() {
    const serviceType = document.getElementById('serviceType');
    const technicians = document.getElementById('technicians');
    const hours = document.getElementById('hours');
    const calculateBtn = document.getElementById('calculateBtn');
    const payWithPayPalBtn = document.getElementById('payWithPayPalBtn');
    
    // Track changes to detect when price needs to be recalculated
    let formChanged = false;
    
    [serviceType, technicians, hours].forEach(element => {
        if (element) {
            element.addEventListener('change', function() {
                formChanged = true;
                // Update price display but don't destroy PayPal buttons immediately
                calculatePrice();
                
                // Show warning in PayPal container
                const paypalButtonContainer = document.getElementById('paypal-button-container');
                if (paypalButtonContainer && paypalButtonsInstance) {
                    paypalButtonContainer.innerHTML = 
                        '<div class="payment-message warning">' +
                        'Price has changed. Please recalculate to update payment options.' +
                        '</div>';
                }
            });
        }
    });
    
    // Calculate button
    if (calculateBtn) {
        calculateBtn.addEventListener('click', function() {
            // Get current values
            const serviceTypeValue = document.getElementById('serviceType').value;
            const totalAmount = parseFloat(document.getElementById('totalAmount').value) || 0;
            
            // Check if price has actually changed
            const newPrice = calculatePrice();
            formChanged = false;
            
            if (newPrice > 0 && serviceTypeValue) {
                // Only re-render PayPal if price or service type changed
                if (newPrice !== currentPrice || serviceTypeValue !== currentServiceType) {
                    currentPrice = newPrice;
                    currentServiceType = serviceTypeValue;
                    renderPayPalButtons();
                }
                
                window.showNotification('Price calculated successfully!', 'success');
            } else {
                window.showNotification('Please select a service type and calculate price', 'error');
            }
        });
    }
    
    // Botón Pay with PayPal
    if (payWithPayPalBtn) {
        payWithPayPalBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Validar formulario
            if (!validateForm()) {
                return;
            }
            
            const totalAmount = parseFloat(document.getElementById('totalAmount').value) || 0;
            if (totalAmount <= 0) {
                window.showNotification('Please calculate the price first', 'error');
                return;
            }
            
            // Renderizar botones de PayPal y permitir al usuario hacer clic directamente
            renderPayPalButtons();
        });
    }
    
    // Form submission
    const serviceForm = document.getElementById('serviceForm');
    if (serviceForm) {
        serviceForm.addEventListener('submit', function(e) {
            e.preventDefault();
            window.showNotification('Please use the PayPal button to complete your booking', 'info');
        });
    }
    
    // Initial calculation
    calculatePrice();
}

function initFormValidation() {
    const requiredFields = document.querySelectorAll('#serviceForm [required]');
    requiredFields.forEach(field => {
        field.addEventListener('blur', function() {
            if (!this.value.trim()) {
                this.style.borderColor = 'red';
            } else {
                this.style.borderColor = '#e9ecef';
            }
        });
        
        field.addEventListener('input', function() {
            this.style.borderColor = '#e9ecef';
        });
    });
}

function validateForm() {
    const serviceForm = document.getElementById('serviceForm');
    const requiredFields = serviceForm.querySelectorAll('[required]');
    let isValid = true;
    let firstErrorField = null;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.style.borderColor = 'red';
            
            if (!firstErrorField) {
                firstErrorField = field;
            }
        }
    });
    
    if (!isValid) {
        window.showNotification('Please fill all required fields', 'error');
        
        if (firstErrorField) {
            firstErrorField.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center' 
            });
            firstErrorField.focus();
        }
    }
    
    return isValid;
}

function calculatePrice() {
    const serviceType = document.getElementById('serviceType');
    const technicians = document.getElementById('technicians');
    const hours = document.getElementById('hours');
    const priceServiceType = document.getElementById('priceServiceType');
    const priceWorkersHours = document.getElementById('priceWorkersHours');
    const priceTotal = document.getElementById('priceTotal');
    const submitAmount = document.getElementById('submitAmount');
    const totalAmountInput = document.getElementById('totalAmount');
    const payWithPayPalBtn = document.getElementById('payWithPayPalBtn');
    
    if (!serviceType || !technicians || !hours) return 0;
    
    // Get values
    const serviceTypeValue = serviceType.value;
    const techniciansValue = parseInt(technicians.value) || 0;
    const hoursValue = parseInt(hours.value) || 0;
    
    // Price per hour per technician
    let pricePerHour = 0;
    let serviceName = '';
    
    if (serviceTypeValue === 'maintenance') {
        pricePerHour = 80;
        serviceName = 'Maintenance (£80/hour)';
    } else if (serviceTypeValue === 'installation') {
        pricePerHour = 150;
        serviceName = 'Installation (£150/hour)';
    }
    
    // Calculate total
    const total = pricePerHour * techniciansValue * hoursValue;
    
    // Update display
    if (priceServiceType) {
        priceServiceType.textContent = serviceTypeValue ? serviceName : '-';
    }
    
    if (priceWorkersHours) {
        priceWorkersHours.textContent = serviceTypeValue ? `${techniciansValue} technicians × ${hoursValue} hours` : '-';
    }
    
    if (priceTotal) {
        priceTotal.textContent = serviceTypeValue ? `£${total.toFixed(2)}` : '£0.00';
    }
    
    if (submitAmount) {
        submitAmount.textContent = total.toFixed(2);
    }
    
    if (totalAmountInput) {
        totalAmountInput.value = total.toFixed(2);
    }
    
    // Enable/disable PayPal button
    if (payWithPayPalBtn) {
        if (total > 0 && serviceTypeValue) {
            payWithPayPalBtn.disabled = false;
            payWithPayPalBtn.innerHTML = `Pay with PayPal - £<span id="submitAmount">${total.toFixed(2)}</span>`;
        } else {
            payWithPayPalBtn.disabled = true;
            payWithPayPalBtn.innerHTML = `Pay with PayPal - £<span id="submitAmount">0.00</span>`;
        }
    }
    
    return total;
}

function initDatePicker() {
    const serviceDate = document.getElementById('serviceDate');
    if (!serviceDate) return;
    
    // Set minimum date to tomorrow
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    
    const minDate = tomorrow.toISOString().split('T')[0];
    serviceDate.min = minDate;
    
    // Set default date to tomorrow
    if (!serviceDate.value) {
        serviceDate.value = minDate;
    }
}

function clearPayPalButtons() {
    const paypalButtonContainer = document.getElementById('paypal-button-container');
    if (paypalButtonContainer) {
        paypalButtonContainer.innerHTML = '<div class="paypal-loading">Updating payment options...</div>';
    }
    
    if (paypalButtonsInstance) {
        try {
            paypalButtonsInstance.close();
        } catch (e) {
            // suppressed PayPal close error
        }
        paypalButtonsInstance = null;
    }
}

function renderPayPalButtons() {
    const paypalButtonContainer = document.getElementById('paypal-button-container');
    const totalAmountInput = document.getElementById('totalAmount');
    
    if (!paypalButtonContainer || !totalAmountInput) return;
    
    const totalAmount = parseFloat(totalAmountInput.value) || 0;
    const serviceType = document.getElementById('serviceType').value;
    
    // Only initialize PayPal if there's an amount and service type selected
    if (totalAmount <= 0 || !serviceType) {
        paypalButtonContainer.innerHTML = '<div class="payment-message">Please calculate the price first</div>';
        return;
    }
    
    
    
    // Check if PayPal SDK is loaded
    if (typeof paypal === 'undefined') {
        paypalButtonContainer.innerHTML = '<div class="payment-message error">PayPal SDK failed to load. Please refresh the page.</div>';
        window.showNotification('PayPal not loaded. Please refresh page.', 'error');
        return;
    }
    
    try {
        // Destroy previous PayPal buttons if they exist
        if (paypalButtonsInstance) {
            try {
                paypalButtonsInstance.close();
            } catch (e) {
                // suppressed previous PayPal close error
            }
        }
        
        // Clear container first
        paypalButtonContainer.innerHTML = '';
        
        // Render PayPal buttons
        paypalButtonsInstance = paypal.Buttons({
            style: {
                layout: 'vertical',
                color: 'gold',
                shape: 'rect',
                label: 'paypal',
                height: 48
            },
            
            createOrder: function(data, actions) {
                
                
                // Asegúrate de que el monto sea válido
                if (totalAmount <= 0) {
                    return Promise.reject(new Error('Invalid amount'));
                }
                
                // Get form data
                const serviceTypeValue = document.getElementById('serviceType').value;
                const serviceName = serviceTypeValue === 'maintenance' ? 'Maintenance Service' : 'Installation Service';
                const technicians = document.getElementById('technicians').value;
                const hours = document.getElementById('hours').value;
                
                return actions.order.create({
                    intent: 'CAPTURE',
                    purchase_units: [{
                        description: `${serviceName} - ${technicians} technicians for ${hours} hours`,
                        amount: {
                            value: totalAmount.toFixed(2),
                            currency_code: 'GBP',
                            breakdown: {
                                item_total: {
                                    value: totalAmount.toFixed(2),
                                    currency_code: 'GBP'
                                }
                            }
                        },
                        items: [{
                            name: serviceName,
                            description: `${technicians} technicians for ${hours} hours`,
                            quantity: '1',
                            unit_amount: {
                                value: totalAmount.toFixed(2),
                                currency_code: 'GBP'
                            }
                        }]
                    }],
                    application_context: {
                        shipping_preference: 'NO_SHIPPING',
                        user_action: 'PAY_NOW',
                        brand_name: 'IntecGIB'
                    }
                });
            },
            
            onApprove: function(data, actions) {
                
                
                window.showNotification('Processing payment...', 'info');
                
                // Muestra más detalles
                
                
                return actions.order.capture().then(function(details) {
                    
                    
                    // Verifica que details.id exista
                    if (details.id) {
                        document.getElementById('paypalTransactionId').value = details.id;
                        submitServiceBooking(details);
                    } else {
                        window.showNotification('Payment failed: No transaction ID received', 'error');
                    }
                }).catch(function(err) {
                    window.showNotification('Payment capture failed: ' + err.message, 'error');
                });
            },
            
            onError: function(err) {
                window.showNotification('Payment failed: ' + (err.message || 'Unknown error'), 'error');
            },
            
            onCancel: function(data) {
                window.showNotification('Payment cancelled', 'info');
            },
            
            onClick: function() {
                // Validate form before showing PayPal window
                if (!validateForm()) {
                    return false;
                }
                return true;
            }
        });
        
        // Render the buttons
        paypalButtonsInstance.render('#paypal-button-container').catch(err => {
            paypalButtonContainer.innerHTML = '<div class="payment-message error">Error loading payment options. Please try again.</div>';
        });
        
    } catch (error) {
        paypalButtonContainer.innerHTML = '<div class="payment-message error">Payment system error. Please try again.</div>';
    }
}


function submitServiceBooking(paypalDetails) {
    
    
    const serviceForm = document.getElementById('serviceForm');
    if (!serviceForm) return;
    
    // Show loading state
    const payWithPayPalBtn = document.getElementById('payWithPayPalBtn');
    if (payWithPayPalBtn) {
        payWithPayPalBtn.disabled = true;
        payWithPayPalBtn.textContent = 'Processing...';
    }
    
    // Create FormData object
    const formData = new FormData(serviceForm);
    formData.append('paypalDetails', JSON.stringify(paypalDetails));
    formData.append('ajax', '1');
    
    // Send to server
    fetch('process_service.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        
        
        if (data.success) {
            // Show success modal (include invoice URL if provided)
            showSuccessModal({
                reference: data.reference,
                amount: data.amount,
                invoiceUrl: data.invoice && data.invoice.url ? data.invoice.url : null
            });
            
            // Reset form but keep calculated price
            const savedPrice = document.getElementById('totalAmount').value;
            serviceForm.reset();
            initDatePicker();
            
            // Restore the calculated price
            document.getElementById('totalAmount').value = savedPrice;
            
            // Re-enable button
            if (payWithPayPalBtn) {
                payWithPayPalBtn.disabled = false;
                payWithPayPalBtn.innerHTML = `Pay with PayPal - £<span id="submitAmount">${savedPrice}</span>`;
            }
            
            // Clear PayPal buttons and show success message
            const paypalButtonContainer = document.getElementById('paypal-button-container');
            if (paypalButtonContainer) {
                paypalButtonContainer.innerHTML = 
                    '<div class="payment-message success">' +
                    'Payment completed successfully! You can book another service.' +
                    '</div>';
            }
            
            // Reset PayPal instance
            paypalButtonsInstance = null;
            
            // Show success notification
            window.showNotification('Service booked successfully! Reference: ' + data.reference, 'success');
            
        } else {
            throw new Error(data.message || 'Unknown server error');
        }
    })
    .catch(error => {
        // suppressed submission error logging
        window.showNotification('Error processing booking: ' + error.message, 'error');
        
        // Re-enable button
        if (payWithPayPalBtn) {
            payWithPayPalBtn.disabled = false;
            payWithPayPalBtn.innerHTML = `Pay with PayPal - £<span id="submitAmount">${document.getElementById('totalAmount').value}</span>`;
        }
    });
}

function showSuccessModal(data) {
    const modal = document.getElementById('successModal');
    const referenceNumber = document.getElementById('referenceNumber');
    const confirmedAmount = document.getElementById('confirmedAmount');
    
    if (!modal || !referenceNumber || !confirmedAmount) return;
    
    // Set data
    referenceNumber.textContent = data.reference || 'N/A';
    confirmedAmount.textContent = `£${data.amount || '0.00'}`;
    
    // Invoice actions (download / print)
    const invoiceActions = document.getElementById('invoiceActions');
    if (invoiceActions) {
        invoiceActions.innerHTML = '';
        if (data.invoiceUrl) {
            // Create download button (anchor)
            const dl = document.createElement('a');
            dl.href = data.invoiceUrl;
            dl.className = 'cta-button';
            dl.textContent = 'Download Invoice (PDF)';
            dl.setAttribute('download', '');
            dl.style.marginRight = '8px';

            // Create print button: open PDF view in browser (request inline view)
            const pr = document.createElement('button');
            pr.className = 'cta-button secondary';
            pr.textContent = 'Print Invoice';
            pr.onclick = function() {
                // Open PDF in new tab using inline view parameter
                const separator = data.invoiceUrl.indexOf('?') === -1 ? '?' : '&';
                const viewUrl = data.invoiceUrl + separator + 'view=1';
                const w = window.open(viewUrl, '_blank');
                if (!w) {
                    window.showNotification('Pop-up blocked. Please allow popups to view the invoice.', 'error');
                    return;
                }
                // Do not auto-print; user can use browser print from PDF viewer
            };

            invoiceActions.appendChild(dl);
            invoiceActions.appendChild(pr);
        } else {
            invoiceActions.innerHTML = '<p class="note">Invoice will be available shortly via email.</p>';
        }
    }
    
    // Show modal
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    // Close button
    const closeBtn = modal.querySelector('.close');
    if (closeBtn) {
        closeBtn.onclick = function() {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        };
    }
    
    // Close when clicking outside
    window.onclick = function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    };
    
    // Close with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.style.display === 'block') {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });
}

// Hacer funciones disponibles globalmente
window.calculatePrice = calculatePrice;
window.renderPayPalButtons = renderPayPalButtons;
window.triggerPayPalButton = triggerPayPalButton;