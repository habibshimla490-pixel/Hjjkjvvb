/**
 * WinyPay Bangladesh Payment Gateway - Frontend Integration
 * Integrates with existing Wallet HTML UI
 * 
 * This file handles:
 * - Deposit initiation and redirection
 * - Withdrawal processing with account binding
 * - Wallet balance updates
 * - Error handling and user feedback
 */

const WinyPayIntegration = (() => {
  // Configuration
  const API_BASE = '/api/payment';
  const USER_ID = getUserIdFromSession(); // Implement based on your auth system

  // ====================================================
  // UTILITY FUNCTIONS
  // ====================================================

  function getUserIdFromSession() {
    // Replace with your actual user ID retrieval logic
    // This might come from:
    // - localStorage
    // - sessionStorage
    // - Cookie
    // - Server-side session
    return localStorage.getItem('user_id') || sessionStorage.getItem('user_id') || 1;
  }

  function makeRequest(endpoint, method = 'GET', data = null) {
    return new Promise((resolve, reject) => {
      const options = {
        method: method,
        headers: {
          'Content-Type': 'application/json',
        },
      };

      if (data && method !== 'GET') {
        options.body = JSON.stringify(data);
      }

      fetch(`${API_BASE}${endpoint}`, options)
        .then(response => {
          if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
          }
          return response.json();
        })
        .then(data => resolve(data))
        .catch(error => {
          console.error('API Error:', error);
          reject(error);
        });
    });
  }

  function showMessage(message, type = 'info') {
    // type: 'info', 'success', 'error', 'warning'
    const messageDiv = document.createElement('div');
    messageDiv.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      padding: 16px 20px;
      border-radius: 8px;
      font-weight: 600;
      z-index: 10001;
      animation: slideIn 0.3s ease;
      max-width: 400px;
    `;

    const colors = {
      'success': '#3ddc84',
      'error': '#ff5c5c',
      'warning': '#f5c518',
      'info': '#a7f542',
    };

    messageDiv.style.backgroundColor = colors[type] || colors['info'];
    messageDiv.style.color = type === 'warning' ? '#0d0f10' : '#fff';
    messageDiv.textContent = message;

    document.body.appendChild(messageDiv);

    setTimeout(() => {
      messageDiv.remove();
    }, 4000);
  }

  // ====================================================
  // DEPOSIT FUNCTIONALITY
  // ====================================================

  async function initiateDeposit(amount, paymentMethod) {
    try {
      if (!amount || amount <= 0) {
        showMessage('Please enter a valid amount', 'error');
        return false;
      }

      if (!paymentMethod) {
        showMessage('Please select a payment method', 'error');
        return false;
      }

      // Show loading state
      const depositBtn = document.getElementById('depositBtn');
      if (depositBtn) {
        depositBtn.disabled = true;
        depositBtn.textContent = 'Processing...';
      }

      // Call API to initiate deposit
      const response = await makeRequest('/deposit.php', 'POST', {
        user_id: USER_ID,
        amount: parseFloat(amount),
        payment_method: paymentMethod.toLowerCase(),
      });

      if (response.success && response.pay_url) {
        // Store order ID in session for reference
        sessionStorage.setItem('last_deposit_order', response.order_id);

        showMessage('Redirecting to payment gateway...', 'success');

        // Redirect to payment URL after short delay
        setTimeout(() => {
          window.location.href = response.pay_url;
        }, 1500);

        return true;
      } else {
        showMessage(response.message || 'Deposit initiation failed', 'error');
        return false;
      }
    } catch (error) {
      showMessage('Error: ' + error.message, 'error');
      return false;
    } finally {
      const depositBtn = document.getElementById('depositBtn');
      if (depositBtn) {
        depositBtn.disabled = false;
        depositBtn.textContent = 'Deposit ৳' + (document.getElementById('amountDisplay')?.textContent || '100').replace('৳', '');
      }
    }
  }

  // ====================================================
  // WITHDRAWAL FUNCTIONALITY
  // ====================================================

  async function bindPaymentAccount(paymentMethod, accountName, accountNumber) {
    try {
      if (!paymentMethod || !accountName || !accountNumber) {
        showMessage('Please fill in all fields', 'error');
        return false;
      }

      const response = await makeRequest('/bind-account.php', 'POST', {
        user_id: USER_ID,
        payment_method: paymentMethod,
        account_name: accountName,
        account_number: accountNumber,
      });

      if (response.success) {
        showMessage('Account bound successfully!', 'success');

        // Update UI to show bound account
        updateBoundAccountUI(paymentMethod, accountNumber);

        // Close modal
        const modal = document.getElementById('bindAccountModal');
        if (modal) {
          modal.classList.remove('open');
        }

        // Enable withdrawal button
        const withdrawBtn = document.getElementById('withdrawBtn');
        if (withdrawBtn) {
          withdrawBtn.classList.remove('disabled');
          withdrawBtn.removeAttribute('disabled');
        }

        return true;
      } else {
        showMessage(response.message || 'Failed to bind account', 'error');
        return false;
      }
    } catch (error) {
      showMessage('Error: ' + error.message, 'error');
      return false;
    }
  }

  function updateBoundAccountUI(paymentMethod, accountNumber) {
    const unboundState = document.getElementById('unboundState');
    const boundState = document.getElementById('boundState');
    const boundPhoneDisplay = document.getElementById('boundPhoneDisplay');
    const boundChannelIcon = document.getElementById('boundChannelIcon');

    if (unboundState) unboundState.style.display = 'none';
    if (boundState) boundState.style.display = 'flex';
    if (boundPhoneDisplay) boundPhoneDisplay.textContent = accountNumber;

    // Update icon based on payment method
    const iconMap = {
      'bkash': 'https://resource.betfugu02.com/paymethodicon/EWALLET_BKASH.png',
      'nagad': 'https://resource.betfugu02.com/paymethodicon/EWALLET_NAGAD.png',
      'rocket': 'https://resource.betfugu02.com/paymethodicon/bdt_rocket.webp',
      'usdt': 'https://resource.betfugu02.com/paymethodicon/usdt.png',
    };

    if (boundChannelIcon && iconMap[paymentMethod.toLowerCase()]) {
      boundChannelIcon.src = iconMap[paymentMethod.toLowerCase()];
    }
  }

  async function initiateWithdraw(amount, paymentMethod, accountName, accountNumber) {
    try {
      if (!amount || amount <= 0) {
        showMessage('Please enter a valid amount', 'error');
        return false;
      }

      if (!paymentMethod || !accountName || !accountNumber) {
        showMessage('Please select payment method and bind account', 'error');
        return false;
      }

      // Show loading state
      const withdrawBtn = document.getElementById('withdrawBtn');
      if (withdrawBtn) {
        withdrawBtn.disabled = true;
        withdrawBtn.textContent = 'Processing...';
      }

      // Call API to initiate withdrawal
      const response = await makeRequest('/withdraw.php', 'POST', {
        user_id: USER_ID,
        amount: parseFloat(amount),
        payment_method: paymentMethod.toLowerCase(),
        account_name: accountName,
        account_number: accountNumber,
      });

      if (response.success) {
        showMessage('Withdrawal request submitted! Awaiting confirmation.', 'success');
        sessionStorage.setItem('last_withdraw_order', response.order_id);

        // Reset form
        resetWithdrawalForm();

        // Refresh wallet balance
        await refreshWalletBalance();

        return true;
      } else {
        showMessage(response.message || 'Withdrawal failed', 'error');
        return false;
      }
    } catch (error) {
      showMessage('Error: ' + error.message, 'error');
      return false;
    } finally {
      const withdrawBtn = document.getElementById('withdrawBtn');
      if (withdrawBtn) {
        withdrawBtn.disabled = false;
        withdrawBtn.textContent = 'Withdraw';
      }
    }
  }

  function resetWithdrawalForm() {
    const withdrawAmountBtns = document.querySelectorAll('.withdraw-amount-btn');
    withdrawAmountBtns.forEach(btn => btn.classList.remove('active'));
  }

  // ====================================================
  // WALLET MANAGEMENT
  // ====================================================

  async function refreshWalletBalance() {
    try {
      const response = await makeRequest(`/wallet.php?user_id=${USER_ID}`);

      if (response.success && response.wallet) {
        updateWalletUI(response.wallet);
      }
    } catch (error) {
      console.error('Failed to refresh wallet:', error);
    }
  }

  function updateWalletUI(walletData) {
    // Update deposit page balance
    const depositAmountDisplay = document.getElementById('amountDisplay');
    if (depositAmountDisplay) {
      depositAmountDisplay.textContent = '৳' + walletData.balance.toLocaleString('en-BD', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
    }

    // Update withdrawal page balance
    const withdrawAmountDisplay = document.getElementById('withdrawAmountDisplay');
    if (withdrawAmountDisplay) {
      withdrawAmountDisplay.textContent = '৳' + walletData.balance.toLocaleString('en-BD', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
    }

    // Update withdrawal details
    const withdrawableVal = document.getElementById('withdrawableVal');
    if (withdrawableVal) {
      withdrawableVal.textContent = walletData.balance.toLocaleString();
    }

    const dailyLimitVal = document.getElementById('dailyLimitVal');
    if (dailyLimitVal) {
      dailyLimitVal.textContent = walletData.daily_limit.toLocaleString();
    }
  }

  // ====================================================
  // EVENT LISTENERS - ATTACH TO EXISTING UI
  // ====================================================

  function attachEventListeners() {
    // ========== DEPOSIT PAGE EVENTS ==========

    // Amount button clicks
    document.querySelectorAll('#depositPage .amount-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const amount = this.getAttribute('data-amt');
        // Amount selection is already handled by existing code
        // This just ensures deposit handler gets the right amount
      });
    });

    // Deposit button click
    const depositBtn = document.getElementById('depositBtn');
    if (depositBtn) {
      depositBtn.addEventListener('click', async function(e) {
        e.preventDefault();

        const amount = document.getElementById('amountDisplay')?.textContent.replace('৳', '').trim();
        const activeMethod = document.querySelector('#depositPage .method-btn.active');
        const paymentMethod = activeMethod?.getAttribute('data-method');

        if (!paymentMethod) {
          showMessage('Please select a payment method', 'error');
          return;
        }

        const methodMap = {
          '1': 'bkash',
          '2': 'nagad',
          '3': 'rocket',
          '4': 'usdt',
        };

        await initiateDeposit(amount, methodMap[paymentMethod]);
      });
    }

    // ========== WITHDRAWAL PAGE EVENTS ==========

    // Withdrawal amount selection
    document.querySelectorAll('.withdraw-amount-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const amount = this.getAttribute('data-wamt');
        const commissionAmount = (amount * 1) / 100; // 1% commission

        // Update displays
        document.getElementById('withdrawableVal').textContent = amount;
        document.getElementById('commissionVal').textContent = Math.round(commissionAmount).toLocaleString();
      });
    });

    // Bind Account Modal
    const openBindModalBtn = document.getElementById('openBindModalBtn');
    const closeBindModalBtn = document.getElementById('closeBindModalBtn');
    const editBindBtn = document.getElementById('editBindBtn');
    const bindAccountModal = document.getElementById('bindAccountModal');

    if (openBindModalBtn) {
      openBindModalBtn.addEventListener('click', () => {
        if (bindAccountModal) bindAccountModal.classList.add('open');
      });
    }

    if (closeBindModalBtn) {
      closeBindModalBtn.addEventListener('click', () => {
        if (bindAccountModal) bindAccountModal.classList.remove('open');
      });
    }

    if (editBindBtn) {
      editBindBtn.addEventListener('click', () => {
        if (bindAccountModal) bindAccountModal.classList.add('open');
      });
    }

    // Submit bind form
    const submitBindFormBtn = document.getElementById('submitBindFormBtn');
    if (submitBindFormBtn) {
      submitBindFormBtn.addEventListener('click', async function(e) {
        e.preventDefault();

        const activeMethod = document.querySelector('.bind-method-card.active');
        const paymentMethod = activeMethod?.getAttribute('data-channel');
        const accountName = document.getElementById('bindNameInput')?.value;
        const accountNumber = document.getElementById('bindPhoneInput')?.value;

        if (accountName && accountNumber) {
          const success = await bindPaymentAccount(paymentMethod, accountName, accountNumber);

          if (success) {
            // Clear form
            document.getElementById('bindNameInput').value = '';
            document.getElementById('bindPhoneInput').value = '';
          }
        }
      });
    }

    // Withdrawal button click
    const withdrawBtn = document.getElementById('withdrawBtn');
    if (withdrawBtn) {
      withdrawBtn.addEventListener('click', async function(e) {
        e.preventDefault();

        if (this.disabled || this.classList.contains('disabled')) {
          showMessage('Please bind your account first', 'warning');
          return;
        }

        const amount = document.getElementById('withdrawAmountDisplay')?.textContent.replace('৳', '').trim();
        const boundPhone = document.getElementById('boundPhoneDisplay')?.textContent;
        const boundIcon = document.getElementById('boundChannelIcon')?.src;

        if (!amount || !boundPhone || !boundIcon) {
          showMessage('Please select amount and bind account', 'error');
          return;
        }

        // Extract payment method from icon URL
        let paymentMethod = 'bkash';
        if (boundIcon.includes('nagad')) paymentMethod = 'nagad';
        else if (boundIcon.includes('rocket')) paymentMethod = 'rocket';
        else if (boundIcon.includes('usdt')) paymentMethod = 'usdt';

        await initiateWithdraw(
          amount,
          paymentMethod,
          'User', // Account name - update if you have this
          boundPhone
        );
      });
    }
  }

  // ====================================================
  // PUBLIC API
  // ====================================================

  return {
    init: function() {
      console.log('WinyPay Integration initialized');
      attachEventListeners();
      // Load wallet balance on init
      refreshWalletBalance();
    },
    initiateDeposit: initiateDeposit,
    bindPaymentAccount: bindPaymentAccount,
    initiateWithdraw: initiateWithdraw,
    refreshWalletBalance: refreshWalletBalance,
    showMessage: showMessage,
  };
})();

// Initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    WinyPayIntegration.init();
  });
} else {
  WinyPayIntegration.init();
}
