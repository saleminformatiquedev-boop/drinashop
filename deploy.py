import ftplib
import os
import sys

FTP_HOST = "147.93.93.153"
FTP_USER = "u676885592"
FTP_PASS = "Fathia1986!123"
FTP_ROOT_DIR = "/domains/drinashopkerkennah.shop/public_html/drinashop"

LOCAL_DIR = "/Users/salemkammoun/Documents/salemk/drinashop"

# Extensions and folders to completely ignore during upload
IGNORE_DIRS = {".git", ".gemini", "node_modules", "vendor", ".DS_Store"}
IGNORE_FILES = {".DS_Store", "deploy.py"}
IGNORE_EXTS = {".sqlite", ".db", ".log"}

def connect_ftp():
    print("Connecting to FTP...")
    ftp = ftplib.FTP()
    ftp.set_debuglevel(1)
    ftp.connect(FTP_HOST, timeout=10)
    ftp.login(FTP_USER, FTP_PASS)
    return ftp

def ftp_mkdirs(ftp, path):
    parts = path.strip("/").split("/")
    current_path = ""
    for part in parts:
        if not part: continue
        current_path = current_path + "/" + part if current_path else "/" + part
        try:
            ftp.cwd(current_path)
        except ftplib.error_perm:
            try:
                ftp.mkd(current_path)
                print(f"Created remote directory: {current_path}")
            except ftplib.error_perm as e:
                print(f"Failed to create directory {current_path}: {e}")

def upload_directory(ftp, local_dir, remote_dir):
    ftp_mkdirs(ftp, remote_dir)
    ftp.cwd(remote_dir)
    
    for item in os.listdir(local_dir):
        if item in IGNORE_DIRS or item in IGNORE_FILES:
            continue
            
        local_path = os.path.join(local_dir, item)
        remote_path = f"{remote_dir}/{item}".replace("//", "/")
        
        if os.path.isdir(local_path):
            upload_directory(ftp, local_path, remote_path)
            ftp.cwd(remote_dir) # Go back to current remote dir
        else:
            ext = os.path.splitext(item)[1].lower()
            if ext in IGNORE_EXTS:
                print(f"Skipped sensitive file: {item}")
                continue
                
            print(f"Uploading {item} to {remote_dir}...")
            with open(local_path, 'rb') as file:
                try:
                    ftp.storbinary(f"STOR {item}", file)
                except Exception as e:
                    print(f"Error uploading {item}: {e}")

if __name__ == "__main__":
    print("Starting deployment...")
    try:
        ftp = connect_ftp()
        print("Connected to FTP server.")
        upload_directory(ftp, LOCAL_DIR, FTP_ROOT_DIR)
        ftp.quit()
        print("Deployment successful.")
    except Exception as e:
        print(f"Deployment failed: {e}")
        sys.exit(1)
