import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { 
    ArrowLeft, 
    Edit, 
    Download, 
    Copy,
    Calendar,
    User,
    FileText,
    Image as ImageIcon,
    Video,
    Music,
    Archive
} from 'lucide-react';
import AdminLayout from '@/layouts/Admin/admin-layout';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

interface Media {
    id: number;
    name: string;
    file_name: string;
    original_name: string;
    path: string;
    mime_type: string;
    type: string;
    size: number;
    width?: number;
    height?: number;
    alt_text?: string;
    description?: string;
    is_active: boolean;
    url: string;
    size_for_humans: string;
    is_image: boolean;
    uploader: {
        id: number;
        name: string;
    };
    created_at: string;
    updated_at: string;
}

interface Props {
    media: Media;
}

const typeIcons = {
    image: ImageIcon,
    document: FileText,
    video: Video,
    audio: Music,
    other: Archive,
};

const typeColors = {
    image: 'bg-green-100 text-green-800',
    document: 'bg-blue-100 text-blue-800',
    video: 'bg-purple-100 text-purple-800',
    audio: 'bg-yellow-100 text-yellow-800',
    other: 'bg-gray-100 text-gray-800',
};

export default function Show({ media }: Props) {
    const TypeIcon = typeIcons[media.type as keyof typeof typeIcons];

    const copyToClipboard = (text: string) => {
        navigator.clipboard.writeText(text);
        // You could add a toast notification here
    };

    const downloadFile = () => {
        const link = document.createElement('a');
        link.href = media.url;
        link.download = media.original_name;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

    return (
        <AdminLayout>
            <Head title={`Media: ${media.name}`} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Button variant="outline" size="sm" asChild>
                            <Link href={route('admin.media.index')}>
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Back to Media
                            </Link>
                        </Button>
                        <div>
                            <div className="flex items-center gap-3">
                                <h1 className="text-3xl font-bold tracking-tight">{media.name}</h1>
                                <Badge variant={media.is_active ? 'default' : 'secondary'}>
                                    {media.is_active ? 'Active' : 'Inactive'}
                                </Badge>
                                <Badge className={typeColors[media.type as keyof typeof typeColors]}>
                                    <TypeIcon className="h-3 w-3 mr-1" />
                                    {media.type}
                                </Badge>
                            </div>
                            <p className="text-muted-foreground mt-1">
                                {media.description || 'No description provided'}
                            </p>
                        </div>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" onClick={downloadFile}>
                            <Download className="h-4 w-4 mr-2" />
                            Download
                        </Button>
                        <Button asChild>
                            <Link href={route('admin.media.edit', media.id)}>
                                <Edit className="h-4 w-4 mr-2" />
                                Edit
                            </Link>
                        </Button>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Media Preview */}
                    <div className="lg:col-span-2">
                        <Card>
                            <CardHeader>
                                <CardTitle>Preview</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="bg-gray-50 rounded-lg overflow-hidden">
                                    {media.is_image ? (
                                        <img
                                            src={media.url}
                                            alt={media.alt_text || media.name}
                                            className="w-full h-auto max-h-96 object-contain"
                                        />
                                    ) : media.type === 'video' ? (
                                        <video
                                            controls
                                            className="w-full h-auto max-h-96"
                                            src={media.url}
                                        >
                                            Your browser does not support the video tag.
                                        </video>
                                    ) : media.type === 'audio' ? (
                                        <div className="p-8 text-center">
                                            <Music className="h-16 w-16 mx-auto text-gray-400 mb-4" />
                                            <audio controls className="w-full max-w-md">
                                                <source src={media.url} type={media.mime_type} />
                                                Your browser does not support the audio element.
                                            </audio>
                                        </div>
                                    ) : (
                                        <div className="p-12 text-center">
                                            <TypeIcon className="h-16 w-16 mx-auto text-gray-400 mb-4" />
                                            <p className="text-gray-600">Preview not available</p>
                                            <p className="text-sm text-gray-500 mt-1">
                                                Click download to view this file
                                            </p>
                                        </div>
                                    )}
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Media Details */}
                    <div>
                        <Card>
                            <CardHeader>
                                <CardTitle>File Details</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div>
                                    <h4 className="text-sm font-medium text-gray-700">Original Name</h4>
                                    <p className="text-sm text-gray-900 break-all">{media.original_name}</p>
                                </div>

                                <div>
                                    <h4 className="text-sm font-medium text-gray-700">File Name</h4>
                                    <div className="flex items-center gap-2">
                                        <p className="text-sm text-gray-900 break-all flex-1">{media.file_name}</p>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            onClick={() => copyToClipboard(media.file_name)}
                                        >
                                            <Copy className="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>

                                <div>
                                    <h4 className="text-sm font-medium text-gray-700">URL</h4>
                                    <div className="flex items-center gap-2">
                                        <p className="text-sm text-gray-900 break-all flex-1">{media.url}</p>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            onClick={() => copyToClipboard(media.url)}
                                        >
                                            <Copy className="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>

                                <div>
                                    <h4 className="text-sm font-medium text-gray-700">MIME Type</h4>
                                    <p className="text-sm text-gray-900">{media.mime_type}</p>
                                </div>

                                <div>
                                    <h4 className="text-sm font-medium text-gray-700">File Size</h4>
                                    <p className="text-sm text-gray-900">{media.size_for_humans}</p>
                                </div>

                                {media.width && media.height && (
                                    <div>
                                        <h4 className="text-sm font-medium text-gray-700">Dimensions</h4>
                                        <p className="text-sm text-gray-900">{media.width} × {media.height} pixels</p>
                                    </div>
                                )}

                                <div>
                                    <h4 className="text-sm font-medium text-gray-700 flex items-center gap-2">
                                        <User className="h-4 w-4" />
                                        Uploaded By
                                    </h4>
                                    <p className="text-sm text-gray-900">{media.uploader.name}</p>
                                </div>

                                <div>
                                    <h4 className="text-sm font-medium text-gray-700 flex items-center gap-2">
                                        <Calendar className="h-4 w-4" />
                                        Upload Date
                                    </h4>
                                    <p className="text-sm text-gray-900">
                                        {new Date(media.created_at).toLocaleDateString()} at{' '}
                                        {new Date(media.created_at).toLocaleTimeString()}
                                    </p>
                                </div>

                                {media.updated_at !== media.created_at && (
                                    <div>
                                        <h4 className="text-sm font-medium text-gray-700">Last Modified</h4>
                                        <p className="text-sm text-gray-900">
                                            {new Date(media.updated_at).toLocaleDateString()} at{' '}
                                            {new Date(media.updated_at).toLocaleTimeString()}
                                        </p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {media.alt_text && (
                            <Card className="mt-6">
                                <CardHeader>
                                    <CardTitle>Alt Text</CardTitle>
                                    <CardDescription>For accessibility and SEO</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-sm text-gray-900">{media.alt_text}</p>
                                </CardContent>
                            </Card>
                        )}
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
