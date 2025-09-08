import React, { useState, useCallback } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { 
    ArrowLeft, 
    Upload, 
    X, 
    File,
    Image,
    FileText,
    Video,
    Music,
    Archive
} from 'lucide-react';
import AdminLayout from '@/layouts/Admin/admin-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

interface FileWithPreview extends File {
    preview?: string;
    progress?: number;
    error?: string;
}

const typeIcons = {
    image: Image,
    document: FileText,
    video: Video,
    audio: Music,
    other: Archive,
};

export default function Create() {
    const [files, setFiles] = useState<FileWithPreview[]>([]);
    const [isDragOver, setIsDragOver] = useState(false);

    const { data, setData, post, processing, errors } = useForm({
        files: [] as File[],
        name: '',
        alt_text: '',
        description: '',
        is_active: true,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        const formData = new FormData();
        files.forEach((file, index) => {
            formData.append(`files[${index}]`, file);
        });
        
        if (data.name) formData.append('name', data.name);
        if (data.alt_text) formData.append('alt_text', data.alt_text);
        if (data.description) formData.append('description', data.description);
        formData.append('is_active', data.is_active ? '1' : '0');

        post(route('admin.media.store'));
    };

    const getFileType = (file: File): string => {
        const mimeType = file.type;
        if (mimeType.startsWith('image/')) return 'image';
        if (mimeType.startsWith('video/')) return 'video';
        if (mimeType.startsWith('audio/')) return 'audio';
        if (mimeType.includes('pdf') || mimeType.includes('document') || mimeType.includes('word')) return 'document';
        return 'other';
    };

    const formatFileSize = (bytes: number): string => {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    };

    const createFilePreview = (file: File): Promise<FileWithPreview> => {
        return new Promise((resolve) => {
            const fileWithPreview = file as FileWithPreview;
            
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    fileWithPreview.preview = e.target?.result as string;
                    resolve(fileWithPreview);
                };
                reader.readAsDataURL(file);
            } else {
                resolve(fileWithPreview);
            }
        });
    };

    const handleFilesAdded = useCallback(async (newFiles: FileList | File[]) => {
        const fileArray = Array.from(newFiles);
        const validFiles = fileArray.filter(file => {
            // Check file size (10MB max)
            if (file.size > 10 * 1024 * 1024) {
                return false;
            }
            return true;
        });

        const filesWithPreviews = await Promise.all(
            validFiles.map(file => createFilePreview(file))
        );

        setFiles(prev => [...prev, ...filesWithPreviews]);
        setData('files', [...files, ...validFiles]);
    }, [files, setData]);

    const handleDrop = useCallback((e: React.DragEvent) => {
        e.preventDefault();
        setIsDragOver(false);
        
        if (e.dataTransfer.files) {
            handleFilesAdded(e.dataTransfer.files);
        }
    }, [handleFilesAdded]);

    const handleDragOver = useCallback((e: React.DragEvent) => {
        e.preventDefault();
        setIsDragOver(true);
    }, []);

    const handleDragLeave = useCallback((e: React.DragEvent) => {
        e.preventDefault();
        setIsDragOver(false);
    }, []);

    const handleFileInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        if (e.target.files) {
            handleFilesAdded(e.target.files);
        }
    };

    const removeFile = (index: number) => {
        const newFiles = files.filter((_, i) => i !== index);
        setFiles(newFiles);
        setData('files', newFiles);
    };

    return (
        <AdminLayout>
            <Head title="Upload Media" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center gap-4">
                    <Button variant="outline" size="sm" asChild>
                        <Link href={route('admin.media.index')}>
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back to Media
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-2xl font-semibold text-gray-900">Upload Media</h1>
                        <p className="mt-1 text-sm text-gray-600">
                            Upload images, documents, videos, and other files
                        </p>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* File Upload Area */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Choose Files</CardTitle>
                            <CardDescription>
                                Drag and drop files here or click to browse. Maximum file size: 10MB.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div
                                className={`border-2 border-dashed rounded-lg p-8 text-center transition-colors ${
                                    isDragOver 
                                        ? 'border-blue-500 bg-blue-50' 
                                        : 'border-gray-300 hover:border-gray-400'
                                }`}
                                onDrop={handleDrop}
                                onDragOver={handleDragOver}
                                onDragLeave={handleDragLeave}
                            >
                                <Upload className="mx-auto h-12 w-12 text-gray-400" />
                                <div className="mt-4">
                                    <Label htmlFor="file-input" className="cursor-pointer">
                                        <span className="text-blue-600 hover:text-blue-700 font-medium">
                                            Click to upload
                                        </span>
                                        <span className="text-gray-600"> or drag and drop</span>
                                    </Label>
                                    <input
                                        id="file-input"
                                        type="file"
                                        multiple
                                        onChange={handleFileInputChange}
                                        className="hidden"
                                        accept="image/*,video/*,audio/*,.pdf,.doc,.docx"
                                    />
                                </div>
                                <p className="text-sm text-gray-500 mt-2">
                                    PNG, JPG, GIF, MP4, PDF, DOC up to 10MB each
                                </p>
                            </div>

                            {errors.files && (
                                <p className="mt-2 text-sm text-red-600">{errors.files}</p>
                            )}
                        </CardContent>
                    </Card>

                    {/* File List */}
                    {files.length > 0 && (
                        <Card>
                            <CardHeader>
                                <CardTitle>Selected Files ({files.length})</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {files.map((file, index) => {
                                        const fileType = getFileType(file);
                                        const TypeIcon = typeIcons[fileType as keyof typeof typeIcons];
                                        
                                        return (
                                            <div key={index} className="flex items-center gap-4 p-4 border border-gray-200 rounded-lg">
                                                <div className="flex-shrink-0">
                                                    {file.preview ? (
                                                        <img 
                                                            src={file.preview} 
                                                            alt={file.name}
                                                            className="h-16 w-16 object-cover rounded-lg"
                                                        />
                                                    ) : (
                                                        <div className="h-16 w-16 bg-gray-100 rounded-lg flex items-center justify-center">
                                                            <TypeIcon className="h-8 w-8 text-gray-400" />
                                                        </div>
                                                    )}
                                                </div>
                                                <div className="flex-1 min-w-0">
                                                    <p className="text-sm font-medium text-gray-900 truncate">
                                                        {file.name}
                                                    </p>
                                                    <p className="text-sm text-gray-500">
                                                        {formatFileSize(file.size)} • {fileType}
                                                    </p>
                                                    {file.type.startsWith('image/') && (
                                                        <p className="text-sm text-gray-500">
                                                            Image file
                                                        </p>
                                                    )}
                                                </div>
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="sm"
                                                    onClick={() => removeFile(index)}
                                                >
                                                    <X className="h-4 w-4" />
                                                </Button>
                                            </div>
                                        );
                                    })}
                                </div>
                            </CardContent>
                        </Card>
                    )}

                    {/* Additional Options */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Additional Options</CardTitle>
                            <CardDescription>
                                Set common properties for all uploaded files
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <Label htmlFor="name">Custom Name (optional)</Label>
                                <Input
                                    id="name"
                                    type="text"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    placeholder="Leave empty to use original filename"
                                />
                                {errors.name && (
                                    <p className="mt-1 text-sm text-red-600">{errors.name}</p>
                                )}
                            </div>

                            <div>
                                <Label htmlFor="alt_text">Alt Text (for images)</Label>
                                <Input
                                    id="alt_text"
                                    type="text"
                                    value={data.alt_text}
                                    onChange={(e) => setData('alt_text', e.target.value)}
                                    placeholder="Describe the image for accessibility"
                                />
                                {errors.alt_text && (
                                    <p className="mt-1 text-sm text-red-600">{errors.alt_text}</p>
                                )}
                            </div>

                            <div>
                                <Label htmlFor="description">Description</Label>
                                <Textarea
                                    id="description"
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    placeholder="Optional description for the media files"
                                    rows={3}
                                />
                                {errors.description && (
                                    <p className="mt-1 text-sm text-red-600">{errors.description}</p>
                                )}
                            </div>

                            <div className="flex items-center space-x-2">
                                <Switch
                                    id="is_active"
                                    checked={data.is_active}
                                    onCheckedChange={(checked) => setData('is_active', checked)}
                                />
                                <Label htmlFor="is_active">Active</Label>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Submit Button */}
                    <div className="flex justify-end">
                        <Button 
                            type="submit" 
                            disabled={processing || files.length === 0}
                            className="min-w-[120px]"
                        >
                            {processing ? 'Uploading...' : `Upload ${files.length} File${files.length !== 1 ? 's' : ''}`}
                        </Button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}
