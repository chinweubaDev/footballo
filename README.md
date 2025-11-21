# Football Prediction App

A comprehensive football prediction platform built with Laravel 12, featuring premium subscriptions, payment integration, and admin management.

## Features

### User Features
- **Homepage**: Today's tips and featured predictions
- **All Predictions**: Browse all available predictions grouped by leagues
- **Premium Tips**: Exclusive predictions for premium subscribers
- **Maxodds Tips**: High-value predictions with maximum odds potential
- **User Dashboard**: Personal dashboard with subscription status
- **Profile Management**: Update personal information and preferences
- **Pricing Plans**: Multiple subscription options with geo-targeting

### Admin Features
- **Admin Dashboard**: Overview of users, payments, and predictions
- **Fixture Management**: Add fixtures from API Football integration
- **Payment Tracking**: Monitor user payments and subscriptions
- **User Management**: View and manage user accounts
- **Prediction Management**: Create and manage predictions

### Technical Features
- **API Football Integration**: Fetch fixtures and match data
- **Flutterwave Payment Integration**: Secure payment processing
- **Email Notifications**: Automated notifications for activities
- **Geo-targeting**: Country-specific pricing
- **Responsive Design**: Mobile-friendly interface
- **Authentication System**: Secure user registration and login

## Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js and NPM
- SQLite (or MySQL/PostgreSQL)

### Setup Instructions

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd football-prediction-app
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure Environment Variables**
   Edit `.env` file and add your API keys:
   ```env
   # API Football Configuration
   API_FOOTBALL_KEY=your_api_football_key_here
   
   # Flutterwave Configuration
   FLUTTERWAVE_PUBLIC_KEY=your_flutterwave_public_key_here
   FLUTTERWAVE_SECRET_KEY=your_flutterwave_secret_key_here
   FLUTTERWAVE_ENCRYPTION_KEY=your_flutterwave_encryption_key_here
   ```

6. **Database Setup**
   ```bash
   php artisan migrate
   ```

7. **Create Admin User**
   ```bash
   php artisan tinker
   ```
   ```php
   $user = new App\Models\User();
   $user->name = 'Admin';
   $user->email = 'admin@footballpredictions.com';
   $user->password = Hash::make('password');
   $user->is_admin = true;
   $user->save();
   ```

8. **Build Assets**
   ```bash
   npm run build
   ```

9. **Start the Application**
   ```bash
   php artisan serve
   ```

## API Keys Setup

### API Football
1. Visit [API Football](https://www.api-football.com/)
2. Sign up for an account
3. Get your API key from the dashboard
4. Add it to your `.env` file

### Flutterwave
1. Visit [Flutterwave](https://flutterwave.com/)
2. Create a merchant account
3. Get your public key, secret key, and encryption key
4. Add them to your `.env` file

## Usage

### Admin Panel
1. Login with admin credentials
2. Navigate to `/admin/dashboard`
3. Use the fixture management to add matches from API Football
4. Create predictions for the fixtures
5. Monitor payments and user activity

### User Experience
1. Users can register and login
2. Browse free predictions on the homepage
3. Subscribe to premium plans for exclusive content
4. Access premium tips and maxodds predictions
5. Manage their profile and subscription

## Database Structure

### Tables
- **users**: User accounts and profiles
- **fixtures**: Match fixtures from API Football
- **predictions**: Predictions linked to fixtures
- **payments**: Payment transactions
- **subscriptions**: User subscription records

### Key Relationships
- Users have many payments and subscriptions
- Fixtures have many predictions
- Payments belong to users and have one subscription

## Payment Plans

The system supports multiple subscription plans:
- **3 Days**: Short-term access
- **1 Week**: Weekly subscription
- **1 Month**: Monthly subscription
- **3 Months**: Quarterly subscription

Pricing is geo-targeted based on user location.

## Email Notifications

The system sends automated emails for:
- New prediction notifications
- Payment confirmations
- Payment failures
- Subscription updates

## Security Features

- CSRF protection
- Password hashing
- Admin middleware protection
- Input validation
- SQL injection prevention

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

This project is licensed under the MIT License.

## Support

For support, email support@footballpredictions.com or create an issue in the repository.

## Roadmap

- [ ] Mobile app development
- [ ] Advanced analytics dashboard
- [ ] Social media integration
- [ ] Live match updates
- [ ] Multi-language support
- [ ] Advanced prediction algorithms