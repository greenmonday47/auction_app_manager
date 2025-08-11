#!/bin/bash

# Payment Gateway Cron Job Setup Script
# This script helps set up the URL-based cron job for checking payment statuses

echo "=== Payment Gateway Cron Job Setup ==="
echo ""

# Get the domain from user
read -p "Enter your domain (e.g., https://your-domain.com): " DOMAIN

if [ -z "$DOMAIN" ]; then
    echo "Error: Domain is required"
    exit 1
fi

# Remove trailing slash if present
DOMAIN=${DOMAIN%/}

# Create the cron job entry
CRON_ENTRY="* * * * * curl -s \"$DOMAIN/auction/public/api/cron/check-payments\" > /dev/null 2>&1"

echo ""
echo "=== Cron Job Configuration ==="
echo "Add the following line to your crontab:"
echo ""
echo "$CRON_ENTRY"
echo ""

# Ask if user wants to add it automatically
read -p "Do you want to add this to your crontab automatically? (y/n): " ADD_CRON

if [ "$ADD_CRON" = "y" ] || [ "$ADD_CRON" = "Y" ]; then
    # Check if the cron entry already exists
    if crontab -l 2>/dev/null | grep -q "api/cron/check-payments"; then
        echo "Cron job already exists. Skipping..."
    else
        # Add to crontab
        (crontab -l 2>/dev/null; echo "$CRON_ENTRY") | crontab -
        echo "Cron job added successfully!"
    fi
else
    echo "Please add the cron entry manually using:"
    echo "crontab -e"
fi

echo ""
echo "=== Health Check ==="
echo "You can test the cron job health by running:"
echo "curl \"$DOMAIN/auction/public/api/cron/health\""
echo ""

echo "=== Manual Testing ==="
echo "To test the payment system manually:"
echo ""
echo "1. Initialize a payment:"
echo "curl -X POST \"$DOMAIN/auction/public/api/payment/initializeTopUp\" \\"
echo "  -H \"Content-Type: application/json\" \\"
echo "  -d '{\"user_id\": 1, \"amount\": 1000}'"
echo ""
echo "2. Check payment status:"
echo "curl \"$DOMAIN/auction/public/api/payment/verify/{transaction_id}\""
echo ""

echo "=== Monitoring ==="
echo "To monitor the cron job:"
echo "1. Check cron logs: tail -f /var/log/cron"
echo "2. Check application logs for payment status updates"
echo "3. Use the health endpoint to verify system status"
echo ""

echo "=== Important Notes ==="
echo "- The cron job runs every minute"
echo "- Ensure your server has curl installed"
echo "- Make sure the domain is accessible from the server"
echo "- Monitor the application logs for any errors"
echo ""

echo "Setup complete!" 