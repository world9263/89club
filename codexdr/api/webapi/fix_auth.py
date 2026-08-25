import os
import re

auth_pattern = re.compile(r"""\s*\$sesquery\s*=\s*"SELECT akshinak\s*FROM shonu_subjects\s*WHERE akshinak = '\$author'";\s*\$sesresult\s*=\s*\$conn->query\(\$sesquery\);\s*\$sesnum\s*=\s*mysqli_num_rows\(\$sesresult\);\s*if\(\$sesnum\s*==\s*1\)""", re.DOTALL)

auth_replacement = """
					$mobile = $data_auth['payload']['mobile'];
					$user = $firebase->get('users/' . $mobile);
					if($user != null && isset($user['akshinak']) && $user['akshinak'] == $author)"""

escape_pattern = re.compile(r"htmlspecialchars\(mysqli_real_escape_string\(\$conn,\s*(.*?)\)\)")
escape_replacement = r"\1"

def process_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    original = content
    content = auth_pattern.sub(auth_replacement, content)
    content = escape_pattern.sub(escape_replacement, content)
    
    if content != original:
        if "global $firebase;" not in content:
            content = content.replace('include "../../functions2.php";', 'include "../../functions2.php";\n\tglobal $firebase;')
            content = content.replace('include "functions2.php";', 'include "functions2.php";\n\tglobal $firebase;')
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Updated {filepath}")

for root, _, files in os.walk('.'):
    for f in files:
        if f.endswith('.php'):
            process_file(os.path.join(root, f))
