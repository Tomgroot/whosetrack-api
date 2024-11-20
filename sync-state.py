import os
import hashlib
import json
import time

def get_file_hash(filepath):
    sha256_hash = hashlib.sha256()
    with open(filepath, "rb") as f:
        for byte_block in iter(lambda: f.read(4096), b""):
            sha256_hash.update(byte_block)
    return sha256_hash.hexdigest()

def generate_json(directory):
    data = []
    for root, dirs, files in os.walk(directory):
        for dir_name in dirs:
            relative_path = os.path.relpath(os.path.join(root, dir_name), directory)
            data.append({
                "type": "folder",
                "name": relative_path
            })
        for file in files:
            filepath = os.path.join(root, file)
            size = os.path.getsize(filepath)
            hash_value = get_file_hash(filepath)
            relative_path = os.path.relpath(filepath, directory)
            data.append({
                "type": "file",
                "name": relative_path,
                "size": size,
                "hash": hash_value
            })
    return {
        "description": ("DO NOT DELETE THIS FILE. This file is used to keep track of "
                        "which files have been synced in the most recent deployment. "
                        "If you delete this file a resync will need to be done (which can take a while) - "
                        "read more: https://github.com/SamKirkland/FTP-Deploy-Action"),
        "version": "1.0.0",
        "generatedTime": int(time.time() * 1000),
        "data": data
    }

if __name__ == "__main__":
    directory_to_scan = "."  # Replace with your directory path
    json_data = generate_json(directory_to_scan)
    with open(".ftp-deploy-sync-state.json", "w") as json_file:
        json.dump(json_data, json_file, indent=4)
