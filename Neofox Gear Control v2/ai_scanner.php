<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Equipment Scanner - Neofox Gear Control</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --neofox-yellow: #FFD700;
            --black-primary: #000000;
            --white-primary: #FFFFFF;
            --green-accent: #4CAF50;
            --blue-accent: #2196F3;
            --red-accent: #FF5722;
            --gray-light: #F5F5F5;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--neofox-yellow);
            color: var(--black-primary);
            min-height: 100vh;
        }

        .navbar {
            background: rgba(0, 0, 0, 0.95);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 900;
            color: var(--neofox-yellow);
            text-decoration: none;
        }

        .navbar-nav {
            display: flex;
            gap: 1rem;
        }

        .nav-link {
            color: var(--white-primary);
            text-decoration: none;
            font-weight: 600;
            padding: 0.6rem 1.2rem;
            border-radius: 20px;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .nav-link:hover, .nav-link.active {
            background: var(--neofox-yellow);
            color: var(--black-primary);
        }

        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 900;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            font-size: 1.1rem;
            color: #333;
        }

        .scanner-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .scanner-card {
            background: var(--white-primary);
            border: 3px solid var(--black-primary);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 8px 8px 0px var(--black-primary);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--gray-light);
        }

        .card-icon {
            width: 60px;
            height: 60px;
            background: var(--black-primary);
            color: var(--neofox-yellow);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .card-title {
            font-size: 1.3rem;
            font-weight: 800;
        }

        .camera-container {
            position: relative;
            width: 100%;
            height: 300px;
            border: 3px dashed var(--black-primary);
            border-radius: 12px;
            overflow: hidden;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .camera-placeholder {
            text-align: center;
            color: #666;
        }

        #cameraFeed {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .capture-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        .corner-marker {
            position: absolute;
            width: 30px;
            height: 30px;
            border: 3px solid var(--neofox-yellow);
        }

        .corner-marker.top-left {
            top: 10px;
            left: 10px;
            border-right: none;
            border-bottom: none;
        }

        .corner-marker.top-right {
            top: 10px;
            right: 10px;
            border-left: none;
            border-bottom: none;
        }

        .corner-marker.bottom-left {
            bottom: 10px;
            left: 10px;
            border-right: none;
            border-top: none;
        }

        .corner-marker.bottom-right {
            bottom: 10px;
            right: 10px;
            border-left: none;
            border-top: none;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border: 2px solid var(--black-primary);
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: var(--neofox-yellow);
            color: var(--black-primary);
            box-shadow: 4px 4px 0px var(--black-primary);
        }

        .btn-primary:hover {
            transform: translateY(-2px) translateX(-2px);
            box-shadow: 6px 6px 0px var(--black-primary);
        }

        .btn-success {
            background: var(--green-accent);
            color: var(--white-primary);
            box-shadow: 4px 4px 0px var(--black-primary);
        }

        .btn-upload {
            background: var(--blue-accent);
            color: var(--white-primary);
            box-shadow: 4px 4px 0px var(--black-primary);
        }

        .upload-area {
            border: 3px dashed var(--black-primary);
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            background: var(--gray-light);
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .upload-area:hover {
            background: #e0e0e0;
        }

        .upload-area.dragover {
            border-color: var(--blue-accent);
            background: #e3f2fd;
        }

        .uploaded-images {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 1rem;
            margin: 1rem 0;
        }

        .uploaded-image {
            position: relative;
            aspect-ratio: 1;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid var(--black-primary);
        }

        .uploaded-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .remove-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background: var(--red-accent);
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
        }

        .detection-results {
            background: var(--white-primary);
            border: 3px solid var(--black-primary);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 8px 8px 0px var(--black-primary);
            margin-top: 2rem;
        }

        .result-item {
            background: var(--gray-light);
            border: 2px solid var(--black-primary);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        .result-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .detected-item {
            font-size: 1.2rem;
            font-weight: 800;
        }

        .confidence-score {
            background: var(--green-accent);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .result-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .detail-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .detail-label {
            font-weight: 600;
            color: #666;
            font-size: 0.9rem;
        }

        .detail-value {
            font-weight: 700;
            color: var(--black-primary);
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--black-primary);
            border-radius: 8px;
            font-size: 1rem;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--neofox-yellow);
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.2);
        }

        .loading {
            text-align: center;
            padding: 2rem;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid var(--gray-light);
            border-top: 4px solid var(--neofox-yellow);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
            border: 2px solid;
        }

        .alert-success {
            background: #e8f5e8;
            border-color: var(--green-accent);
            color: #2e7d32;
        }

        .alert-error {
            background: #ffebee;
            border-color: var(--red-accent);
            color: #c62828;
        }

        .alert-info {
            background: #e3f2fd;
            border-color: var(--blue-accent);
            color: #1976d2;
        }

        @media (max-width: 768px) {
            .scanner-grid {
                grid-template-columns: 1fr;
            }

            .result-details {
                grid-template-columns: 1fr;
            }

            .main-container {
                padding: 1rem;
            }

            .page-title {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="index.php" class="navbar-brand">
                <i class="fas fa-cube"></i> NEOFOX GEAR
            </a>
            <div class="navbar-nav">
                <a href="index.php" class="nav-link">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="assets.php" class="nav-link">
                    <i class="fas fa-box"></i> Assets
                </a>
                <a href="ai_scanner.php" class="nav-link active">
                    <i class="fas fa-robot"></i> AI Scanner
                </a>
                <a href="add_asset.php" class="nav-link">
                    <i class="fas fa-plus"></i> Manual Add
                </a>
                <a href="logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-robot"></i> AI EQUIPMENT SCANNER
            </h1>
            <p class="page-subtitle">Take photos of equipment and let AI automatically identify and catalog them</p>
        </div>

        <!-- Scanner Grid -->
        <div class="scanner-grid">
            <!-- Live Camera Scanner -->
            <div class="scanner-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-camera"></i>
                    </div>
                    <div>
                        <div class="card-title">Live Camera Scanner</div>
                        <div style="font-size: 0.9rem; color: #666;">Point camera at equipment</div>
                    </div>
                </div>

                <div class="camera-container" id="cameraContainer">
                    <div class="camera-placeholder" id="cameraPlaceholder">
                        <i class="fas fa-camera fa-3x" style="margin-bottom: 1rem; opacity: 0.5;"></i>
                        <p>Click "Start Camera" to begin scanning</p>
                    </div>
                    <video id="cameraFeed" style="display: none;" autoplay playsinline></video>
                    <div class="capture-overlay" style="display: none;">
                        <div class="corner-marker top-left"></div>
                        <div class="corner-marker top-right"></div>
                        <div class="corner-marker bottom-left"></div>
                        <div class="corner-marker bottom-right"></div>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                    <button class="btn btn-primary" id="startCameraBtn" onclick="startCamera()">
                        <i class="fas fa-video"></i> Start Camera
                    </button>
                    <button class="btn btn-success" id="captureBtn" onclick="capturePhoto()" style="display: none;">
                        <i class="fas fa-camera"></i> Capture Photo
                    </button>
                    <button class="btn btn-upload" id="stopCameraBtn" onclick="stopCamera()" style="display: none;">
                        <i class="fas fa-stop"></i> Stop Camera
                    </button>
                </div>
            </div>

            <!-- Upload Multiple Photos -->
            <div class="scanner-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <div>
                        <div class="card-title">Upload Photos</div>
                        <div style="font-size: 0.9rem; color: #666;">Upload multiple angles of equipment</div>
                    </div>
                </div>

                <div class="upload-area" id="uploadArea" onclick="document.getElementById('photoInput').click()">
                    <i class="fas fa-cloud-upload-alt fa-3x" style="margin-bottom: 1rem; opacity: 0.5;"></i>
                    <p><strong>Click to upload photos</strong> or drag and drop</p>
                    <p style="font-size: 0.9rem; color: #666;">Multiple angles give better results</p>
                </div>

                <input type="file" id="photoInput" multiple accept="image/*" style="display: none;" onchange="handleFileSelect(event)">

                <div class="uploaded-images" id="uploadedImages"></div>

                <button class="btn btn-success" id="analyzeBtn" onclick="analyzePhotos()" style="display: none; width: 100%;">
                    <i class="fas fa-brain"></i> Analyze with AI
                </button>
            </div>
        </div>

        <!-- Detection Results -->
        <div class="detection-results" id="detectionResults" style="display: none;">
            <div class="card-header">
                <div class="card-icon">
                    <i class="fas fa-brain"></i>
                </div>
                <div>
                    <div class="card-title">AI Detection Results</div>
                    <div style="font-size: 0.9rem; color: #666;">Review and confirm detected equipment</div>
                </div>
            </div>

            <div id="resultsContainer">
                <!-- Results will be populated by JavaScript -->
            </div>
        </div>
    </div>

    <script>
        class AIEquipmentScanner {
            constructor() {
                this.stream = null;
                this.uploadedFiles = [];
                this.canvas = document.createElement('canvas');
                this.setupDragDrop();
            }

            setupDragDrop() {
                const uploadArea = document.getElementById('uploadArea');
                
                uploadArea.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    uploadArea.classList.add('dragover');
                });

                uploadArea.addEventListener('dragleave', () => {
                    uploadArea.classList.remove('dragover');
                });

                uploadArea.addEventListener('drop', (e) => {
                    e.preventDefault();
                    uploadArea.classList.remove('dragover');
                    this.handleFiles(e.dataTransfer.files);
                });
            }

            async startCamera() {
                try {
                    this.stream = await navigator.mediaDevices.getUserMedia({ 
                        video: { 
                            facingMode: 'environment',
                            width: { ideal: 1280 },
                            height: { ideal: 720 }
                        } 
                    });
                    
                    const video = document.getElementById('cameraFeed');
                    const placeholder = document.getElementById('cameraPlaceholder');
                    const overlay = document.querySelector('.capture-overlay');
                    
                    video.srcObject = this.stream;
                    video.style.display = 'block';
                    placeholder.style.display = 'none';
                    overlay.style.display = 'block';
                    
                    document.getElementById('startCameraBtn').style.display = 'none';
                    document.getElementById('captureBtn').style.display = 'inline-flex';
                    document.getElementById('stopCameraBtn').style.display = 'inline-flex';
                    
                } catch (error) {
                    console.error('Error accessing camera:', error);
                    this.showAlert('error', 'Failed to access camera. Please ensure you have a camera connected and have granted permissions.');
                }
            }

            stopCamera() {
                if (this.stream) {
                    this.stream.getTracks().forEach(track => track.stop());
                    this.stream = null;
                }
                
                const video = document.getElementById('cameraFeed');
                const placeholder = document.getElementById('cameraPlaceholder');
                const overlay = document.querySelector('.capture-overlay');
                
                video.style.display = 'none';
                placeholder.style.display = 'block';
                overlay.style.display = 'none';
                
                document.getElementById('startCameraBtn').style.display = 'inline-flex';
                document.getElementById('captureBtn').style.display = 'none';
                document.getElementById('stopCameraBtn').style.display = 'none';
            }

            capturePhoto() {
                const video = document.getElementById('cameraFeed');
                const canvas = this.canvas;
                const ctx = canvas.getContext('2d');
                
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                ctx.drawImage(video, 0, 0);
                
                canvas.toBlob((blob) => {
                    const file = new File([blob], `camera_capture_${Date.now()}.jpg`, { type: 'image/jpeg' });
                    this.uploadedFiles.push(file);
                    this.displayUploadedImages();
                    this.showAlert('success', 'Photo captured! Take more from different angles for better AI detection.');
                }, 'image/jpeg', 0.8);
            }

            handleFiles(files) {
                Array.from(files).forEach(file => {
                    if (file.type.startsWith('image/')) {
                        this.uploadedFiles.push(file);
                    }
                });
                this.displayUploadedImages();
            }

            displayUploadedImages() {
                const container = document.getElementById('uploadedImages');
                const analyzeBtn = document.getElementById('analyzeBtn');
                
                container.innerHTML = '';
                
                this.uploadedFiles.forEach((file, index) => {
                    const imageDiv = document.createElement('div');
                    imageDiv.className = 'uploaded-image';
                    
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    
                    const removeBtn = document.createElement('button');
                    removeBtn.className = 'remove-image';
                    removeBtn.innerHTML = '×';
                    removeBtn.onclick = () => this.removeImage(index);
                    
                    imageDiv.appendChild(img);
                    imageDiv.appendChild(removeBtn);
                    container.appendChild(imageDiv);
                });
                
                analyzeBtn.style.display = this.uploadedFiles.length > 0 ? 'inline-flex' : 'none';
            }

            removeImage(index) {
                this.uploadedFiles.splice(index, 1);
                this.displayUploadedImages();
            }

            async analyzePhotos() {
                if (this.uploadedFiles.length === 0) {
                    this.showAlert('error', 'Please upload at least one photo first.');
                    return;
                }

                this.showLoading();

                try {
                    // Simulate AI analysis with realistic delay
                    await this.simulateAIAnalysis();
                    
                    // Generate mock results based on uploaded images
                    const results = this.generateMockResults();
                    this.displayResults(results);
                    
                } catch (error) {
                    console.error('Analysis error:', error);
                    this.showAlert('error', 'AI analysis failed. Please try again.');
                    this.hideLoading();
                }
            }

            async simulateAIAnalysis() {
                // Simulate AI processing time
                return new Promise(resolve => {
                    setTimeout(resolve, 3000 + Math.random() * 2000);
                });
            }

            generateMockResults() {
                // Mock AI results - in real implementation, this would come from actual AI service
                const equipmentTypes = [
                    {
                        name: 'Sony A7 III Camera',
                        category: 'Camera',
                        confidence: 0.94,
                        brand: 'Sony',
                        model: 'A7 III',
                        type: 'Mirrorless Camera',
                        features: ['Full Frame', '24.2MP', '4K Video', 'Image Stabilization']
                    },
                    {
                        name: 'Canon EOS R5',
                        category: 'Camera', 
                        confidence: 0.89,
                        brand: 'Canon',
                        model: 'EOS R5',
                        type: 'Mirrorless Camera',
                        features: ['Full Frame', '45MP', '8K Video', 'Dual Pixel AF']
                    },
                    {
                        name: 'Rode VideoMic Pro Plus',
                        category: 'Audio',
                        confidence: 0.92,
                        brand: 'Rode',
                        model: 'VideoMic Pro Plus',
                        type: 'Shotgun Microphone',
                        features: ['Rechargeable Battery', 'Auto Sensing', 'Safety Channel']
                    }
                ];

                // Return a random result for demo
                return [equipmentTypes[Math.floor(Math.random() * equipmentTypes.length)]];
            }

            displayResults(results) {
                const container = document.getElementById('resultsContainer');
                const resultsSection = document.getElementById('detectionResults');
                
                container.innerHTML = '';
                
                results.forEach((result, index) => {
                    const resultDiv = document.createElement('div');
                    resultDiv.className = 'result-item';
                    resultDiv.innerHTML = `
                        <div class="result-header">
                            <div class="detected-item">${result.name}</div>
                            <div class="confidence-score">${Math.round(result.confidence * 100)}% confident</div>
                        </div>
                        
                        <div class="result-details">
                            <div class="detail-group">
                                <div class="detail-label">Brand</div>
                                <div class="detail-value">${result.brand}</div>
                            </div>
                            <div class="detail-group">
                                <div class="detail-label">Model</div>
                                <div class="detail-value">${result.model}</div>
                            </div>
                            <div class="detail-group">
                                <div class="detail-label">Category</div>
                                <div class="detail-value">${result.category}</div>
                            </div>
                            <div class="detail-group">
                                <div class="detail-label">Type</div>
                                <div class="detail-value">${result.type}</div>
                            </div>
                        </div>
                        
                        <div class="detail-group" style="margin-bottom: 1rem;">
                            <div class="detail-label">Detected Features</div>
                            <div class="detail-value">${result.features.join(', ')}</div>
                        </div>
                        
                        <form onsubmit="addToInventory(event, ${index})">
                            <div class="form-group">
                                <label class="form-label">Asset Name (editable)</label>
                                <input type="text" class="form-control" name="asset_name" value="${result.name}" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Serial Number (if visible)</label>
                                <input type="text" class="form-control" name="serial_number" placeholder="Enter if visible on equipment">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Additional Notes</label>
                                <textarea class="form-control" name="notes" rows="2" placeholder="Any additional observations...">AI Detected: ${result.brand} ${result.model}</textarea>
                            </div>
                            
                            <div style="display: flex; gap: 1rem;">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-plus"></i> Add to Inventory
                                </button>
                                <button type="button" class="btn btn-upload" onclick="rejectDetection(${index})">
                                    <i class="fas fa-times"></i> Not Correct
                                </button>
                            </div>
                        </form>
                    `;
                    container.appendChild(resultDiv);
                });
                
                resultsSection.style.display = 'block';
                this.hideLoading();
                
                // Scroll to results
                resultsSection.scrollIntoView({ behavior: 'smooth' });
            }

            showLoading() {
                const resultsSection = document.getElementById('detectionResults');
                const container = document.getElementById('resultsContainer');
                
                container.innerHTML = `
                    <div class="loading">
                        <div class="spinner"></div>
                        <h3>AI Analyzing Equipment...</h3>
                        <p>Processing images and identifying equipment type, brand, and model</p>
                    </div>
                `;
                
                resultsSection.style.display = 'block';
            }

            hideLoading() {
                // Loading will be replaced by results
            }

            showAlert(type, message) {
                const alert = document.createElement('div');
                alert.className = `alert alert-${type}`;
                alert.innerHTML = `<i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation-triangle' : 'info'}-circle"></i> ${message}`;
                
                document.body.appendChild(alert);
                alert.style.position = 'fixed';
                alert.style.top = '20px';
                alert.style.right = '20px';
                alert.style.zIndex = '9999';
                alert.style.maxWidth = '400px';
                
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.parentNode.removeChild(alert);
                    }
                }, 5000);
            }
        }

        // Global instance
        const scanner = new AIEquipmentScanner();

        // Global functions
        function startCamera() {
            scanner.startCamera();
        }

        function stopCamera() {
            scanner.stopCamera();
        }

        function capturePhoto() {
            scanner.capturePhoto();
        }

        function handleFileSelect(event) {
            scanner.handleFiles(event.target.files);
        }

        function analyzePhotos() {
            scanner.analyzePhotos();
        }

        async function addToInventory(event, resultIndex) {
            event.preventDefault();
            const formData = new FormData(event.target);
            const data = Object.fromEntries(formData);
            
            // Add detected category
            data.category = event.target.closest('.result-item').querySelector('.detail-value').textContent;
            
            try {
                // In real implementation, send to your PHP backend
                console.log('Adding to inventory:', data);
                
                // Simulate API call
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                scanner.showAlert('success', `${data.asset_name} added to inventory successfully!`);
                
                // Hide the form after successful addition
                event.target.style.display = 'none';
                
                // You could redirect to assets page or refresh the inventory
                // window.location.href = 'assets.php';
                
            } catch (error) {
                scanner.showAlert('error', 'Failed to add equipment to inventory');
            }
        }

        function rejectDetection(index) {
            scanner.showAlert('info', 'Detection rejected. You can try taking photos from different angles or add manually.');
        }

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (scanner.stream) {
                scanner.stream.getTracks().forEach(track => track.stop());
            }
        });
    </script>
</body>
</html>