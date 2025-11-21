// Football Predictions App JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuButton = document.querySelector('[aria-controls="mobile-menu"]');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function() {
            const expanded = mobileMenuButton.getAttribute('aria-expanded') === 'true';
            mobileMenuButton.setAttribute('aria-expanded', !expanded);
            mobileMenu.classList.toggle('hidden');
        });
    }

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.bg-green-100, .bg-red-100, .bg-yellow-100');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease-out';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });

    // Payment form handling
    const paymentForms = document.querySelectorAll('form[data-payment-form]');
    paymentForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
            }
        });
    });

    // Filter functionality for predictions
    const leagueFilter = document.getElementById('leagueFilter');
    const categoryFilter = document.getElementById('categoryFilter');
    
    if (leagueFilter) {
        leagueFilter.addEventListener('change', filterPredictions);
    }
    
    if (categoryFilter) {
        categoryFilter.addEventListener('change', filterPredictions);
    }

    function filterPredictions() {
        // This would implement client-side filtering
        console.log('Filtering predictions...');
    }

    // Smooth scrolling for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Form validation
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('border-red-500');
                    isValid = false;
                } else {
                    field.classList.remove('border-red-500');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    });
});

// Utility functions
function showLoading(element) {
    if (element) {
        element.innerHTML = '<div class="spinner mx-auto"></div>';
    }
}

function hideLoading(element, originalContent) {
    if (element && originalContent) {
        element.innerHTML = originalContent;
    }
}

// Payment initialization
function initializePayment(planType, amount, currency, paymentMethod, cryptoType = null) {
    // Make AJAX request to initialize payment
    fetch('/payment/initialize', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            plan_type: planType,
            amount: amount,
            currency: currency,
            payment_method: paymentMethod,
            crypto_type: cryptoType
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Redirect to appropriate payment page
            window.location.href = data.data.checkout_url;
        } else {
            alert('Payment initialization failed. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}

// Admin Fixture Management Functions
function showAddFixtureModal() {
    document.getElementById('addFixtureModal').classList.remove('hidden');
    showStep1();
}

function hideAddFixtureModal() {
    document.getElementById('addFixtureModal').classList.add('hidden');
}

function showStep1() {
    document.getElementById('step1').classList.remove('hidden');
    document.getElementById('step2').classList.add('hidden');
    document.getElementById('step3').classList.add('hidden');
}

function showStep2() {
    document.getElementById('step1').classList.add('hidden');
    document.getElementById('step2').classList.remove('hidden');
    document.getElementById('step3').classList.add('hidden');
}

function showStep3() {
    document.getElementById('step1').classList.add('hidden');
    document.getElementById('step2').classList.add('hidden');
    document.getElementById('step3').classList.remove('hidden');
}

function fetchFixtures() {
    const country = document.getElementById('country').value;
    const league = document.getElementById('league').value;
    const season = document.getElementById('season').value;
    const date = document.getElementById('date').value;

    if (!country || !league || !season || !date) {
        alert('Please fill in all fields');
        return;
    }

    const loadingDiv = document.getElementById('fixturesList');
    loadingDiv.innerHTML = '<div class="text-center py-8"><div class="spinner mx-auto mb-4"></div><p>Fetching fixtures...</p></div>';

    fetch('/admin/fixtures/fetch', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            country: country,
            league: league,
            season: season,
            date: date
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            displayFixtures(data.fixtures);
            showStep2();
        } else {
            loadingDiv.innerHTML = '<div class="text-center py-8 text-red-600"><p>Error: ' + data.message + '</p></div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        loadingDiv.innerHTML = '<div class="text-center py-8 text-red-600"><p>An error occurred while fetching fixtures</p></div>';
    });
}

function displayFixtures(fixtures) {
    const container = document.getElementById('fixturesList');
    if (!fixtures || !Array.isArray(fixtures)) {
        container.innerHTML = '<div class="text-center py-8 text-red-500"><p>Error: Invalid fixtures data received.</p></div>';
        return;
    }
    
    if (fixtures.length === 0) {
        container.innerHTML = '<div class="text-center py-8 text-gray-500"><p>No fixtures found for the selected criteria</p></div>';
        return;
    }

    let html = '<div class="space-y-2">';
    fixtures.forEach(fixture => {
        // Check if fixture has the expected structure
        if (!fixture.fixture || !fixture.teams || !fixture.league) {
            html += `
                <div class="border border-red-200 rounded-lg p-4">
                    <div class="text-red-500">
                        <p>Invalid fixture data structure</p>
                    </div>
                </div>
            `;
            return;
        }
        
        const matchDate = new Date(fixture.fixture.date);
        const formattedDate = matchDate.toLocaleDateString() + ' ' + matchDate.toLocaleTimeString();
        
        html += `
            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 cursor-pointer" onclick="selectFixture(${JSON.stringify(fixture).replace(/"/g, '&quot;')})">
                <div class="flex justify-between items-center">
                    <div>
                        <h4 class="font-semibold">${fixture.teams.home.name} vs ${fixture.teams.away.name}</h4>
                        <p class="text-sm text-gray-600">${fixture.league.name} - ${fixture.league.country}</p>
                        <p class="text-sm text-gray-500">${formattedDate}</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            ${fixture.fixture.status.long}
                        </span>
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';
    container.innerHTML = html;
}

function selectFixture(fixture) {
    document.getElementById('selectedFixture').value = JSON.stringify(fixture);
    showStep3();
}

function editFixture(fixtureId) {
    // Fetch fixture data and populate edit modal
    fetch(`/admin/fixtures/${fixtureId}`)
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            const fixture = data.fixture;
            
            // Populate form fields
            document.getElementById('editFixtureId').value = fixture.id;
            document.getElementById('editHomeTeam').value = fixture.home_team;
            document.getElementById('editAwayTeam').value = fixture.away_team;
            document.getElementById('editLeagueName').value = fixture.league_name;
            
            // Format date for datetime-local input
            const matchDate = new Date(fixture.match_date);
            const formattedDate = matchDate.toISOString().slice(0, 16);
            document.getElementById('editMatchDate').value = formattedDate;
            
            // Set checkboxes
            document.getElementById('edit_today_tip').checked = fixture.today_tip;
            document.getElementById('edit_featured').checked = fixture.featured;
            document.getElementById('edit_maxodds_tip').checked = fixture.maxodds_tip;
            
            // Show modal
            document.getElementById('editFixtureModal').classList.remove('hidden');
        } else {
            alert('Error loading fixture: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while loading the fixture');
    });
}

function hideEditFixtureModal() {
    document.getElementById('editFixtureModal').classList.add('hidden');
}

function deleteFixture(fixtureId) {
    if (confirm('Are you sure you want to delete this fixture?')) {
        fetch(`/admin/fixtures/${fixtureId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                location.reload();
            } else {
                alert('Error deleting fixture: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the fixture');
        });
    }
}

// Handle add prediction form submission
document.addEventListener('DOMContentLoaded', function() {
    const addPredictionForm = document.getElementById('addPredictionForm');
    if (addPredictionForm) {
        addPredictionForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const fixtureData = JSON.parse(document.getElementById('selectedFixture').value);
            
            const data = {
                fixture_data: fixtureData,
                category: formData.get('category'),
                tip: formData.get('tip'),
                confidence: formData.get('confidence'),
                odds: formData.get('odds'),
                analysis: formData.get('analysis'),
                today_tip: formData.has('today_tip'),
                featured: formData.has('featured'),
                maxodds_tip: formData.has('maxodds_tip'),
                is_premium: formData.has('is_premium')
            };

            fetch('/admin/fixtures/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Fixture and prediction added successfully!');
                    location.reload();
                } else {
                    alert('Error adding fixture: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding the fixture');
            });
        });
    }

    // Handle edit fixture form submission
    const editFixtureForm = document.getElementById('editFixtureForm');
    if (editFixtureForm) {
        editFixtureForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const fixtureId = formData.get('fixture_id');
            
            const data = {
                home_team: formData.get('home_team'),
                away_team: formData.get('away_team'),
                league_name: formData.get('league_name'),
                match_date: formData.get('match_date'),
                today_tip: formData.has('edit_today_tip'),
                featured: formData.has('edit_featured'),
                maxodds_tip: formData.has('edit_maxodds_tip')
            };

            fetch(`/admin/fixtures/${fixtureId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Fixture updated successfully!');
                    hideEditFixtureModal();
                    location.reload();
                } else {
                    alert('Error updating fixture: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the fixture');
            });
        });
    }
});

// User Management Functions
function upgradeUser(userId) {
    document.getElementById('userId').value = userId;
    document.getElementById('upgradeModal').classList.remove('hidden');
}

function hideUpgradeModal() {
    document.getElementById('upgradeModal').classList.add('hidden');
}

function viewUserDetails(userId, name, email, phone, country, isPremium, premiumExpires, createdAt, updatedAt) {
    const content = `
        <div class="space-y-6">
            <div class="flex items-center space-x-4">
                <div class="h-16 w-16 rounded-full bg-gray-300 flex items-center justify-center">
                    <i class="fas fa-user text-gray-600 text-2xl"></i>
                </div>
                <div>
                    <h4 class="text-xl font-semibold text-gray-900">${name}</h4>
                    <p class="text-gray-600">${email}</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h5 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Contact Information</h5>
                    <div class="mt-2 space-y-2">
                        <p><span class="font-medium">Email:</span> ${email}</p>
                        <p><span class="font-medium">Phone:</span> ${phone || 'Not provided'}</p>
                        <p><span class="font-medium">Country:</span> ${country || 'Not specified'}</p>
                    </div>
                </div>
                
                <div>
                    <h5 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Account Status</h5>
                    <div class="mt-2 space-y-2">
                        <p><span class="font-medium">Premium:</span> 
                            ${isPremium ? 
                                `<span class="text-green-600 font-semibold">Yes (Expires: ${new Date(premiumExpires).toLocaleDateString()})</span>` : 
                                '<span class="text-gray-600">No</span>'
                            }
                        </p>
                        <p><span class="font-medium">Joined:</span> ${new Date(createdAt).toLocaleDateString()}</p>
                        <p><span class="font-medium">Last Active:</span> ${new Date(updatedAt).toLocaleDateString()}</p>
                    </div>
                </div>
            </div>
            
            <div class="border-t pt-4">
                <h5 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Quick Actions</h5>
                <div class="flex space-x-3">
                    <button onclick="upgradeUser(${userId})" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition duration-150 ease-in-out">
                        <i class="fas fa-crown mr-2"></i>Upgrade User
                    </button>
                    <button onclick="deactivateUser(${userId}, '${name}')" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition duration-150 ease-in-out">
                        <i class="fas fa-ban mr-2"></i>Deactivate
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('userDetailsContent').innerHTML = content;
    document.getElementById('userDetailsModal').classList.remove('hidden');
}

function hideUserDetailsModal() {
    document.getElementById('userDetailsModal').classList.add('hidden');
}

function deactivateUser(userId, userName) {
    if (confirm(`Are you sure you want to deactivate ${userName}? This will revoke their premium access.`)) {
        fetch(`/admin/users/${userId}/deactivate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert('User deactivated successfully!');
                location.reload();
            } else {
                alert('Error deactivating user: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deactivating the user');
        });
    }
}

// Handle upgrade form submission
document.addEventListener('DOMContentLoaded', function() {
    const upgradeForm = document.getElementById('upgradeForm');
    if (upgradeForm) {
        upgradeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const userId = formData.get('user_id');
            const subscriptionType = formData.get('subscription_type');
            const durationDays = formData.get('duration_days');
            
            fetch(`/admin/users/${userId}/upgrade`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    subscription_type: subscriptionType,
                    duration_days: parseInt(durationDays)
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('User upgraded successfully!');
                    hideUpgradeModal();
                    location.reload();
                } else {
                    alert('Error upgrading user: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while upgrading the user');
            });
        });
    }
});
