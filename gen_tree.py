import os

def generate_tree(startpath, output_file):
    exclude_dirs = {'.git', '.idea', 'vendor', 'node_modules', '__pycache__', '.agent', '.vscode'}
    
    with open(output_file, 'w', encoding='utf-8') as f:
        for root, dirs, files in os.walk(startpath):
            dirs[:] = [d for d in dirs if d not in exclude_dirs]
            dirs.sort()
            files.sort()
            
            level = root.replace(startpath, '').count(os.sep)
            indent = '    ' * level
            if level == 0:
                f.write(f'{os.path.basename(os.path.abspath(root))}/\n')
            else:
                f.write(f'{indent}{os.path.basename(root)}/\n')
            
            subindent = '    ' * (level + 1)
            for file in files:
                f.write(f'{subindent}{file}\n')

if __name__ == '__main__':
    generate_tree('.', 'estructura_actual.txt')
