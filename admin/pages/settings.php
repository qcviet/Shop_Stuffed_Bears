<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">System Settings</h5>
    <button class="btn btn-primary">
        <i class="bi bi-check-circle"></i> Save Changes
    </button>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">General Settings</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="siteName" class="form-label">Site Name</label>
                    <input type="text" class="form-control" id="siteName" value="Shop Gau Yeu">
                </div>
                <div class="mb-3">
                    <label for="siteDescription" class="form-label">Site Description</label>
                    <textarea class="form-control" id="siteDescription" rows="3">Your trusted source for teddy bears and gifts</textarea>
                </div>
                <div class="mb-3">
                    <label for="adminEmail" class="form-label">Admin Email</label>
                    <input type="email" class="form-control" id="adminEmail" value="admin@shopgauyeu.com">
                </div>
                <div class="mb-3">
                    <label for="currency" class="form-label">Currency</label>
                    <select class="form-select" id="currency">
                        <option value="USD" selected>USD ($)</option>
                        <option value="VND">VND (₫)</option>
                        <option value="EUR">EUR (€)</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Email Settings</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="smtpHost" class="form-label">SMTP Host</label>
                    <input type="text" class="form-control" id="smtpHost" value="smtp.gmail.com">
                </div>
                <div class="mb-3">
                    <label for="smtpPort" class="form-label">SMTP Port</label>
                    <input type="number" class="form-control" id="smtpPort" value="587">
                </div>
                <div class="mb-3">
                    <label for="smtpUsername" class="form-label">SMTP Username</label>
                    <input type="text" class="form-control" id="smtpUsername" value="noreply@shopgauyeu.com">
                </div>
                <div class="mb-3">
                    <label for="smtpPassword" class="form-label">SMTP Password</label>
                    <input type="password" class="form-control" id="smtpPassword" value="********">
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Security Settings</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="sessionTimeout" class="form-label">Session Timeout (minutes)</label>
                    <input type="number" class="form-control" id="sessionTimeout" value="30">
                </div>
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="enableCaptcha" checked>
                        <label class="form-check-label" for="enableCaptcha">
                            Enable CAPTCHA on login
                        </label>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="enableTwoFactor" checked>
                        <label class="form-check-label" for="enableTwoFactor">
                            Enable Two-Factor Authentication
                        </label>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="enableBackup" checked>
                        <label class="form-check-label" for="enableBackup">
                            Enable Automatic Backups
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">System Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <small class="text-muted">PHP Version</small>
                        <p class="mb-2"><?php echo phpversion(); ?></p>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">MySQL Version</small>
                        <p class="mb-2">8.0.35</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <small class="text-muted">Server OS</small>
                        <p class="mb-2"><?php echo php_uname('s'); ?></p>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Web Server</small>
                        <p class="mb-2">Apache/2.4.54</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <small class="text-muted">Upload Max Size</small>
                        <p class="mb-2"><?php echo ini_get('upload_max_filesize'); ?></p>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Memory Limit</small>
                        <p class="mb-2"><?php echo ini_get('memory_limit'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 