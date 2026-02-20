#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
API_URL="${API_URL:-http://localhost:8000/api/v1}"
EMAIL="${EMAIL:-aa@test.com}"
PASSWORD="${PASSWORD:-password123}"
CHUNK_SIZE="${CHUNK_SIZE:-1048576}" # Default 1MB

# Global variables
AUTH_TOKEN=""
UPLOAD_ID=""

# Print colored messages
print_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

# Generate unique upload ID
generate_upload_id() {
    echo "upload_$(date +%s)_${RANDOM}"
}

# Format bytes to human-readable
format_bytes() {
    local bytes=$1
    if [ $bytes -lt 1024 ]; then
        echo "${bytes} Bytes"
    elif [ $bytes -lt 1048576 ]; then
        echo "$(awk "BEGIN {printf \"%.2f\", $bytes/1024}") KB"
    elif [ $bytes -lt 1073741824 ]; then
        echo "$(awk "BEGIN {printf \"%.2f\", $bytes/1048576}") MB"
    else
        echo "$(awk "BEGIN {printf \"%.2f\", $bytes/1073741824}") GB"
    fi
}

# Login function
login() {
    print_info "Attempting to login with email: $EMAIL"
    
    response=$(curl -s -w "\n%{http_code}" -X POST "${API_URL}/auth/login" \
        -H "Content-Type: application/json" \
        -H "Accept: application/json" \
        -d "{\"email\":\"${EMAIL}\",\"password\":\"${PASSWORD}\"}")
    
    http_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')
    
    if [ "$http_code" -eq 200 ]; then
        AUTH_TOKEN=$(echo "$body" | jq -r '.data.token')
        
        if [ "$AUTH_TOKEN" != "null" ] && [ -n "$AUTH_TOKEN" ]; then
            print_success "Login successful!"
            print_info "Token: ${AUTH_TOKEN:0:20}..."
            return 0
        else
            print_error "Failed to extract token from response"
            echo "$body" | jq '.'
            return 1
        fi
    else
        print_error "Login failed with HTTP code: $http_code"
        echo "$body" | jq '.'
        return 1
    fi
}

# Upload a single chunk
upload_chunk() {
    local upload_id=$1
    local chunk_index=$2
    local total_chunks=$3
    local chunk_data=$4
    local filename=$5
    local total_size=$6
    local mime_type=$7
    
    print_warning "Uploading chunk $((chunk_index + 1))/$total_chunks..."
    
payload_file=$(mktemp)
jq -n \
    --arg upload_id "$upload_id" \
    --argjson chunk_index "$chunk_index" \
    --argjson total_chunks "$total_chunks" \
    --arg chunk_data "$chunk_data" \
    --arg original_name "$filename" \
    --argjson total_size "$total_size" \
    --arg mime_type "$mime_type" \
    '{upload_id: $upload_id, chunk_index: $chunk_index, total_chunks: $total_chunks, chunk_data: $chunk_data, original_name: $original_name, total_size: $total_size, mime_type: $mime_type}' > "$payload_file"

response=$(curl -s -w "\n%{http_code}" -X POST "${API_URL}/files/upload-chunk" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -H "Authorization: Bearer ${AUTH_TOKEN}" \
    --data @"$payload_file")

rm -f "$payload_file"

    
    http_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')
    
    if [ "$http_code" -eq 200 ]; then
        print_success "Chunk $((chunk_index + 1))/$total_chunks uploaded successfully"
        return 0
    else
        print_error "Failed to upload chunk $((chunk_index + 1))/$total_chunks (HTTP: $http_code)"
        echo "$body" | jq '.'
        return 1
    fi
}

# Complete the upload
complete_upload() {
    local upload_id=$1
    
    print_warning "Completing upload..."
    
    response=$(curl -s -w "\n%{http_code}" -X POST "${API_URL}/files/complete-upload" \
        -H "Content-Type: application/json" \
        -H "Accept: application/json" \
        -H "Authorization: Bearer ${AUTH_TOKEN}" \
        -d "{\"upload_id\": \"${upload_id}\"}")
    
    http_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')
    
    if [ "$http_code" -eq 201 ] || [ "$http_code" -eq 200 ]; then
        print_success "Upload completed successfully!"
        
        file_uuid=$(echo "$body" | jq -r '.data.file.uuid // .data.file.id // "N/A"')
        file_name=$(echo "$body" | jq -r '.data.file.name // "N/A"')
        
        print_success "File UUID: $file_uuid"
        print_success "File Name: $file_name"
        
        echo ""
        print_info "Full response:"
        echo "$body" | jq '.'
        
        return 0
    else
        print_error "Failed to complete upload (HTTP: $http_code)"
        echo "$body" | jq '.'
        return 1
    fi
}

# Main upload function
upload_file() {
    local file_path=$1
    
    # Check if file exists
    if [ ! -f "$file_path" ]; then
        print_error "File not found: $file_path"
        return 1
    fi
    
    # Get file information
    filename=$(basename "$file_path")
    total_size=$(stat -f%z "$file_path" 2>/dev/null || stat -c%s "$file_path" 2>/dev/null)
    mime_type=$(file -b --mime-type "$file_path")
    
    # Calculate chunks
    total_chunks=$(( (total_size + CHUNK_SIZE - 1) / CHUNK_SIZE ))
    
    # Generate upload ID
    UPLOAD_ID=$(generate_upload_id)
    
    print_info "========================================="
    print_info "Starting Chunked Upload"
    print_info "========================================="
    print_info "Upload ID: $UPLOAD_ID"
    print_info "File: $filename"
    print_info "Size: $(format_bytes $total_size)"
    print_info "MIME Type: $mime_type"
    print_info "Chunk Size: $(format_bytes $CHUNK_SIZE)"
    print_info "Total Chunks: $total_chunks"
    print_info "========================================="
    
    # Create temporary directory for chunks
    temp_dir=$(mktemp -d)
    
    # Split file into chunks
    print_info "Splitting file into chunks..."
    split -b $CHUNK_SIZE "$file_path" "${temp_dir}/chunk_"
    
    # Get list of chunk files
    chunk_files=($(ls -1 ${temp_dir}/chunk_* | sort))
    
    # Upload each chunk
    chunk_index=0
    for chunk_file in "${chunk_files[@]}"; do
        print_info "Processing chunk $((chunk_index + 1))/$total_chunks..."
        
        # Read chunk and encode to base64
        chunk_data=$(base64 < "$chunk_file" | tr -d '\n')
        chunk_size=$(stat -f%z "$chunk_file" 2>/dev/null || stat -c%s "$chunk_file" 2>/dev/null)
        
        print_info "Chunk size: $(format_bytes $chunk_size)"
        
        # Upload chunk
        if ! upload_chunk "$UPLOAD_ID" "$chunk_index" "$total_chunks" "$chunk_data" "$filename" "$total_size" "$mime_type"; then
            print_error "Upload failed at chunk $((chunk_index + 1))"
            rm -rf "$temp_dir"
            return 1
        fi
        
        chunk_index=$((chunk_index + 1))
    done
    
    # Clean up temporary files
    rm -rf "$temp_dir"
    
    # Complete the upload
    if complete_upload "$UPLOAD_ID"; then
        print_success "========================================="
        print_success "Upload completed successfully!"
        print_success "========================================="
        return 0
    else
        return 1
    fi
}

# Print usage
usage() {
    echo "Usage: $0 [OPTIONS] <file_path>"
    echo ""
    echo "Options:"
    echo "  -u, --url <url>           API base URL (default: http://localhost:8000/api/v1)"
    echo "  -e, --email <email>       Login email (default: admin@example.com)"
    echo "  -p, --password <password> Login password (default: password)"
    echo "  -c, --chunk-size <bytes>  Chunk size in bytes (default: 1048576 = 1MB)"
    echo "  -h, --help                Show this help message"
    echo ""
    echo "Environment variables:"
    echo "  API_URL                   API base URL"
    echo "  EMAIL                     Login email"
    echo "  PASSWORD                  Login password"
    echo "  CHUNK_SIZE                Chunk size in bytes"
    echo ""
    echo "Examples:"
    echo "  $0 /path/to/file.pdf"
    echo "  $0 -c 524288 /path/to/large-file.zip    # 512KB chunks"
    echo "  API_URL=http://api.example.com/v1 $0 file.jpg"
}

# Check dependencies
check_dependencies() {
    local missing_deps=()
    
    if ! command -v curl &> /dev/null; then
        missing_deps+=("curl")
    fi
    
    if ! command -v jq &> /dev/null; then
        missing_deps+=("jq")
    fi
    
    if ! command -v base64 &> /dev/null; then
        missing_deps+=("base64")
    fi
    
    if [ ${#missing_deps[@]} -ne 0 ]; then
        print_error "Missing required dependencies: ${missing_deps[*]}"
        print_info "Please install them and try again."
        return 1
    fi
    
    return 0
}

# Parse command line arguments
FILE_PATH=""

while [[ $# -gt 0 ]]; do
    case $1 in
        -u|--url)
            API_URL="$2"
            shift 2
            ;;
        -e|--email)
            EMAIL="$2"
            shift 2
            ;;
        -p|--password)
            PASSWORD="$2"
            shift 2
            ;;
        -c|--chunk-size)
            CHUNK_SIZE="$2"
            shift 2
            ;;
        -h|--help)
            usage
            exit 0
            ;;
        *)
            FILE_PATH="$1"
            shift
            ;;
    esac
done

# Main execution
main() {
    echo ""
    print_info "🚀 Chunked Upload Tester"
    echo ""
    
    # Check dependencies
    if ! check_dependencies; then
        exit 1
    fi
    
    # Check if file path is provided
    if [ -z "$FILE_PATH" ]; then
        print_error "No file path provided"
        echo ""
        usage
        exit 1
    fi
    
    # Login
    if ! login; then
        exit 1
    fi
    
    echo ""
    
    # Upload file
    if upload_file "$FILE_PATH"; then
        exit 0
    else
        exit 1
    fi
}

# Run main function
main
