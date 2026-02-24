<?php

namespace Database\Seeders;

use App\Models\LandingPageSection;
use Illuminate\Database\Seeder;

class LandingPageSectionSeeder extends Seeder
{
    /**
     * Available sections: hero, features, about, pricing, testimonials, contact, footer, general
     */
    public function run(): void
    {
        $sections = [
            // General Settings
            ['key' => 'site_name', 'section' => 'general', 'type' => 'text', 'label' => 'Site Name', 'value' => 'Laravel Starter Kit', 'sort_order' => 1],
            ['key' => 'site_tagline', 'section' => 'general', 'type' => 'text', 'label' => 'Site Tagline', 'value' => 'Build Modern Web Applications', 'sort_order' => 2],
            ['key' => 'site_description', 'section' => 'general', 'type' => 'textarea', 'label' => 'Site Description', 'value' => 'The all-in-one platform designed for modern businesses to succeed in the digital age.', 'sort_order' => 3],

            // Hero Section
            ['key' => 'hero_badge_text', 'section' => 'hero', 'type' => 'text', 'label' => 'Hero Badge Text', 'value' => 'New Features Available', 'sort_order' => 10],
            ['key' => 'hero_headline', 'section' => 'hero', 'type' => 'text', 'label' => 'Hero Headline', 'value' => 'Transform Your Business with Smart Solutions', 'sort_order' => 11],
            ['key' => 'hero_subtitle', 'section' => 'hero', 'type' => 'textarea', 'label' => 'Hero Subtitle', 'value' => 'Streamline operations, boost productivity, and scale effortlessly. The all-in-one platform designed for modern businesses to succeed in the digital age.', 'sort_order' => 12],
            ['key' => 'hero_primary_button_text', 'section' => 'hero', 'type' => 'text', 'label' => 'Primary Button Text', 'value' => 'Get Started Free', 'sort_order' => 13],
            ['key' => 'hero_secondary_button_text', 'section' => 'hero', 'type' => 'text', 'label' => 'Secondary Button Text', 'value' => 'Sign In', 'sort_order' => 14],
            ['key' => 'hero_trust_1', 'section' => 'hero', 'type' => 'text', 'label' => 'Trust Badge 1', 'value' => 'No credit card required', 'sort_order' => 15],
            ['key' => 'hero_trust_2', 'section' => 'hero', 'type' => 'text', 'label' => 'Trust Badge 2', 'value' => '14-day free trial', 'sort_order' => 16],

            // Features Section
            ['key' => 'features_title', 'section' => 'features', 'type' => 'text', 'label' => 'Features Title', 'value' => 'Everything You Need to Succeed', 'sort_order' => 20],
            ['key' => 'features_subtitle', 'section' => 'features', 'type' => 'textarea', 'label' => 'Features Subtitle', 'value' => 'Powerful features to help you manage, grow, and scale your business with confidence.', 'sort_order' => 21],
            ['key' => 'feature_1_icon', 'section' => 'features', 'type' => 'text', 'label' => 'Feature 1 Icon', 'value' => 'bi-speedometer2', 'sort_order' => 22],
            ['key' => 'feature_1_title', 'section' => 'features', 'type' => 'text', 'label' => 'Feature 1 Title', 'value' => 'Real-Time Dashboard', 'sort_order' => 23],
            ['key' => 'feature_1_description', 'section' => 'features', 'type' => 'textarea', 'label' => 'Feature 1 Description', 'value' => 'Monitor your business metrics in real-time with intuitive charts and actionable insights at your fingertips.', 'sort_order' => 24],
            ['key' => 'feature_2_icon', 'section' => 'features', 'type' => 'text', 'label' => 'Feature 2 Icon', 'value' => 'bi-shield-check', 'sort_order' => 25],
            ['key' => 'feature_2_title', 'section' => 'features', 'type' => 'text', 'label' => 'Feature 2 Title', 'value' => 'Enterprise Security', 'sort_order' => 26],
            ['key' => 'feature_2_description', 'section' => 'features', 'type' => 'textarea', 'label' => 'Feature 2 Description', 'value' => 'Bank-level encryption and security protocols to keep your data safe and compliant with industry standards.', 'sort_order' => 27],
            ['key' => 'feature_3_icon', 'section' => 'features', 'type' => 'text', 'label' => 'Feature 3 Icon', 'value' => 'bi-people', 'sort_order' => 28],
            ['key' => 'feature_3_title', 'section' => 'features', 'type' => 'text', 'label' => 'Feature 3 Title', 'value' => 'Team Collaboration', 'sort_order' => 29],
            ['key' => 'feature_3_description', 'section' => 'features', 'type' => 'textarea', 'label' => 'Feature 3 Description', 'value' => 'Work seamlessly with your team using role-based access, shared workspaces, and real-time updates.', 'sort_order' => 30],
            ['key' => 'feature_4_icon', 'section' => 'features', 'type' => 'text', 'label' => 'Feature 4 Icon', 'value' => 'bi-graph-up', 'sort_order' => 31],
            ['key' => 'feature_4_title', 'section' => 'features', 'type' => 'text', 'label' => 'Feature 4 Title', 'value' => 'Advanced Analytics', 'sort_order' => 32],
            ['key' => 'feature_4_description', 'section' => 'features', 'type' => 'textarea', 'label' => 'Feature 4 Description', 'value' => 'Deep dive into your data with comprehensive reports, custom filters, and exportable insights.', 'sort_order' => 33],
            ['key' => 'feature_5_icon', 'section' => 'features', 'type' => 'text', 'label' => 'Feature 5 Icon', 'value' => 'bi-gear', 'sort_order' => 34],
            ['key' => 'feature_5_title', 'section' => 'features', 'type' => 'text', 'label' => 'Feature 5 Title', 'value' => 'Customizable Workflows', 'sort_order' => 35],
            ['key' => 'feature_5_description', 'section' => 'features', 'type' => 'textarea', 'label' => 'Feature 5 Description', 'value' => 'Automate repetitive tasks and create custom workflows that adapt to your unique business processes.', 'sort_order' => 36],
            ['key' => 'feature_6_icon', 'section' => 'features', 'type' => 'text', 'label' => 'Feature 6 Icon', 'value' => 'bi-plug', 'sort_order' => 37],
            ['key' => 'feature_6_title', 'section' => 'features', 'type' => 'text', 'label' => 'Feature 6 Title', 'value' => 'Seamless Integrations', 'sort_order' => 38],
            ['key' => 'feature_6_description', 'section' => 'features', 'type' => 'textarea', 'label' => 'Feature 6 Description', 'value' => 'Connect with your favorite tools and services through our extensive API and pre-built integrations.', 'sort_order' => 39],

            // Stats Section
            ['key' => 'stats_users_count', 'section' => 'stats', 'type' => 'text', 'label' => 'Stats Users Count', 'value' => '10K+', 'sort_order' => 40],
            ['key' => 'stats_users_label', 'section' => 'stats', 'type' => 'text', 'label' => 'Stats Users Label', 'value' => 'Active Users', 'sort_order' => 41],
            ['key' => 'stats_uptime_count', 'section' => 'stats', 'type' => 'text', 'label' => 'Stats Uptime Count', 'value' => '99.9%', 'sort_order' => 42],
            ['key' => 'stats_uptime_label', 'section' => 'stats', 'type' => 'text', 'label' => 'Stats Uptime Label', 'value' => 'Uptime', 'sort_order' => 43],
            ['key' => 'stats_integrations_count', 'section' => 'stats', 'type' => 'text', 'label' => 'Stats Integrations Count', 'value' => '50+', 'sort_order' => 44],
            ['key' => 'stats_integrations_label', 'section' => 'stats', 'type' => 'text', 'label' => 'Stats Integrations Label', 'value' => 'Integrations', 'sort_order' => 45],
            ['key' => 'stats_rating_count', 'section' => 'stats', 'type' => 'text', 'label' => 'Stats Rating Count', 'value' => '4.9/5', 'sort_order' => 46],
            ['key' => 'stats_rating_label', 'section' => 'stats', 'type' => 'text', 'label' => 'Stats Rating Label', 'value' => 'User Rating', 'sort_order' => 47],

            // About Section
            ['key' => 'about_title', 'section' => 'about', 'type' => 'text', 'label' => 'About Title', 'value' => 'Built for Modern Businesses', 'sort_order' => 50],
            ['key' => 'about_description', 'section' => 'about', 'type' => 'textarea', 'label' => 'About Description', 'value' => 'We understand the challenges of running a business in today\'s fast-paced world. That\'s why we built our platform to help you work smarter, not harder.', 'sort_order' => 51],
            ['key' => 'about_benefit_1_title', 'section' => 'about', 'type' => 'text', 'label' => 'About Benefit 1 Title', 'value' => 'Easy to Use', 'sort_order' => 52],
            ['key' => 'about_benefit_1_desc', 'section' => 'about', 'type' => 'textarea', 'label' => 'About Benefit 1 Description', 'value' => 'Intuitive interface designed with user experience in mind. No technical expertise required.', 'sort_order' => 53],
            ['key' => 'about_benefit_2_title', 'section' => 'about', 'type' => 'text', 'label' => 'About Benefit 2 Title', 'value' => 'Lightning Fast', 'sort_order' => 54],
            ['key' => 'about_benefit_2_desc', 'section' => 'about', 'type' => 'textarea', 'label' => 'About Benefit 2 Description', 'value' => 'Built on cutting-edge technology for optimal performance and reliability.', 'sort_order' => 55],
            ['key' => 'about_benefit_3_title', 'section' => 'about', 'type' => 'text', 'label' => 'About Benefit 3 Title', 'value' => '24/7 Support', 'sort_order' => 56],
            ['key' => 'about_benefit_3_desc', 'section' => 'about', 'type' => 'textarea', 'label' => 'About Benefit 3 Description', 'value' => 'Our dedicated support team is always ready to help you succeed.', 'sort_order' => 57],

            // Pricing Section
            ['key' => 'pricing_title', 'section' => 'pricing', 'type' => 'text', 'label' => 'Pricing Title', 'value' => 'Simple, Transparent Pricing', 'sort_order' => 60],
            ['key' => 'pricing_subtitle', 'section' => 'pricing', 'type' => 'textarea', 'label' => 'Pricing Subtitle', 'value' => 'Choose the plan that fits your needs. No hidden fees, cancel anytime.', 'sort_order' => 61],
            ['key' => 'pricing_basic_name', 'section' => 'pricing', 'type' => 'text', 'label' => 'Basic Plan Name', 'value' => 'Basic', 'sort_order' => 62],
            ['key' => 'pricing_basic_price', 'section' => 'pricing', 'type' => 'text', 'label' => 'Basic Plan Price', 'value' => '$0', 'sort_order' => 63],
            ['key' => 'pricing_basic_period', 'section' => 'pricing', 'type' => 'text', 'label' => 'Basic Plan Period', 'value' => 'Forever free', 'sort_order' => 64],
            ['key' => 'pricing_pro_name', 'section' => 'pricing', 'type' => 'text', 'label' => 'Pro Plan Name', 'value' => 'Pro', 'sort_order' => 65],
            ['key' => 'pricing_pro_price', 'section' => 'pricing', 'type' => 'text', 'label' => 'Pro Plan Price', 'value' => '$29', 'sort_order' => 66],
            ['key' => 'pricing_pro_period', 'section' => 'pricing', 'type' => 'text', 'label' => 'Pro Plan Period', 'value' => 'per month', 'sort_order' => 67],
            ['key' => 'pricing_enterprise_name', 'section' => 'pricing', 'type' => 'text', 'label' => 'Enterprise Plan Name', 'value' => 'Enterprise', 'sort_order' => 68],
            ['key' => 'pricing_enterprise_price', 'section' => 'pricing', 'type' => 'text', 'label' => 'Enterprise Plan Price', 'value' => '$99', 'sort_order' => 69],
            ['key' => 'pricing_enterprise_period', 'section' => 'pricing', 'type' => 'text', 'label' => 'Enterprise Plan Period', 'value' => 'per month', 'sort_order' => 70],

            // CTA Section
            ['key' => 'cta_title', 'section' => 'cta', 'type' => 'text', 'label' => 'CTA Title', 'value' => 'Ready to Get Started?', 'sort_order' => 80],
            ['key' => 'cta_description', 'section' => 'cta', 'type' => 'textarea', 'label' => 'CTA Description', 'value' => 'Join thousands of businesses already using our platform to grow their business. Start your free trial today.', 'sort_order' => 81],
            ['key' => 'cta_primary_button', 'section' => 'cta', 'type' => 'text', 'label' => 'CTA Primary Button', 'value' => 'Start Free Trial', 'sort_order' => 82],
            ['key' => 'cta_secondary_button', 'section' => 'cta', 'type' => 'text', 'label' => 'CTA Secondary Button', 'value' => 'Contact Sales', 'sort_order' => 83],

            // Contact Section
            ['key' => 'contact_title', 'section' => 'contact', 'type' => 'text', 'label' => 'Contact Title', 'value' => 'Get in Touch', 'sort_order' => 90],
            ['key' => 'contact_subtitle', 'section' => 'contact', 'type' => 'textarea', 'label' => 'Contact Subtitle', 'value' => 'Have questions? We\'d love to hear from you.', 'sort_order' => 91],
            ['key' => 'contact_email', 'section' => 'contact', 'type' => 'text', 'label' => 'Contact Email', 'value' => 'contact@example.com', 'sort_order' => 92],
            ['key' => 'contact_phone', 'section' => 'contact', 'type' => 'text', 'label' => 'Contact Phone', 'value' => '+1 (555) 123-4567', 'sort_order' => 93],
            ['key' => 'contact_address', 'section' => 'contact', 'type' => 'textarea', 'label' => 'Contact Address', 'value' => '123 Business Street, Suite 100, New York, NY 10001', 'sort_order' => 94],

            // Footer Section
            ['key' => 'footer_description', 'section' => 'footer', 'type' => 'textarea', 'label' => 'Footer Description', 'value' => 'The modern platform for businesses to manage, grow, and scale with confidence.', 'sort_order' => 100],
            ['key' => 'footer_copyright', 'section' => 'footer', 'type' => 'text', 'label' => 'Footer Copyright', 'value' => 'Made with love using Laravel & Bootstrap 5', 'sort_order' => 101],
            ['key' => 'footer_facebook', 'section' => 'footer', 'type' => 'text', 'label' => 'Facebook URL', 'value' => '#', 'sort_order' => 102],
            ['key' => 'footer_twitter', 'section' => 'footer', 'type' => 'text', 'label' => 'Twitter URL', 'value' => '#', 'sort_order' => 103],
            ['key' => 'footer_linkedin', 'section' => 'footer', 'type' => 'text', 'label' => 'LinkedIn URL', 'value' => '#', 'sort_order' => 104],
            ['key' => 'footer_github', 'section' => 'footer', 'type' => 'text', 'label' => 'GitHub URL', 'value' => '#', 'sort_order' => 105],
        ];

        foreach ($sections as $section) {
            LandingPageSection::firstOrCreate(
                ['key' => $section['key']],
                $section
            );
        }
    }
}
