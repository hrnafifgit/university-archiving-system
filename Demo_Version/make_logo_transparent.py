from PIL import Image
import numpy as np

# Load image3.jpeg
img = Image.open('uas_backend/public/images/image3.jpeg').convert('RGBA')
data = np.array(img)

# Define white threshold for transparency
# If pixel is very close to white (R>245, G>245, B>245), make it transparent
r, g, b, a = data[:, :, 0], data[:, :, 1], data[:, :, 2], data[:, :, 3]
white_mask = (r > 240) & (g > 240) & (b > 240)

# Smooth edges by feathering
data[white_mask, 3] = 0

trans_img = Image.fromarray(data)
trans_img.save('uas_backend/public/images/ibb_logo.png')
print('Created uas_backend/public/images/ibb_logo.png with transparency')
