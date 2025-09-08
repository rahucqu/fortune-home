import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { 
    ArrowLeft, 
    Save,
    Image as ImageIcon,
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

export default function Edit({ media }: Props) {
    const TypeIcon = typeIcons[media.type as keyof typeof typeIcons];

    const { data, setData, put, processing, errors } = useForm({
        name: media.name,
        alt_text: media.alt_text || '',
        description: media.description || '',
        is_active: media.is_active,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('admin.media.update', media.id));
    };

    return (
        <AdminLayout>
            <Head title={`Edit Media: ${media.name}`} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center gap-4">
                    <Button variant="outline" size="sm" asChild>
                        <Link href={route('admin.media.show', media.id)}>
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back to Media
                        </Link>
                    </Button>
                    <div>
                        <div className="flex items-center gap-3">
                            <h1 className="text-2xl font-semibold text-gray-900">Edit Media</h1>
                            <Badge className={typeColors[media.type as keyof typeof typeColors]}>
                                <TypeIcon className="h-3 w-3 mr-1" />
                                {media.type}
                            </Badge>
                        </div>
                        <p className="mt-1 text-sm text-gray-600">
                            Update media information and properties
                        </p>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Form */}
                    <div className="lg:col-span-2">
                        <form onSubmit={handleSubmit} className="space-y-6">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Media Information</CardTitle>
                                    <CardDescription>
                                        Update the media details and properties
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div>
                                        <Label htmlFor="name">Name</Label>
                                        <Input
                                            id="name"
                                            type="text"
                                            value={data.name}
                                            onChange={(e) => setData('name', e.target.value)}
                                            placeholder="Media name"
                                        />
                                        {errors.name && (
                                            <p className="mt-1 text-sm text-red-600">{errors.name}</p>
                                        )}
                                    </div>

                                    {media.is_image && (
                                        <div>
                                            <Label htmlFor="alt_text">Alt Text</Label>
                                            <Input
                                                id="alt_text"
                                                type="text"
                                                value={data.alt_text}
                                                onChange={(e) => setData('alt_text', e.target.value)}
                                                placeholder="Describe the image for accessibility"
                                            />
                                            <p className="mt-1 text-sm text-gray-500">
                                                Alternative text for screen readers and SEO
                                            </p>
                                            {errors.alt_text && (
                                                <p className="mt-1 text-sm text-red-600">{errors.alt_text}</p>
                                            )}
                                        </div>
                                    )}

                                    <div>
                                        <Label htmlFor="description">Description</Label>
                                        <Textarea
                                            id="description"
                                            value={data.description}
                                            onChange={(e) => setData('description', e.target.value)}
                                            placeholder="Optional description for this media file"
                                            rows={4}
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
                                        <p className="text-sm text-gray-500">
                                            Inactive media files are hidden from public view
                                        </p>
                                    </div>
                                </CardContent>
                            </Card>

                            <div className="flex justify-end">
                                <Button type="submit" disabled={processing}>
                                    <Save className="h-4 w-4 mr-2" />
                                    {processing ? 'Saving...' : 'Save Changes'}
                                </Button>
                            </div>
                        </form>
                    </div>

                    {/* Preview and File Info */}
                    <div>
                        <Card>
                            <CardHeader>
                                <CardTitle>Preview</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="bg-gray-50 rounded-lg overflow-hidden mb-4">
                                    {media.is_image ? (
                                        <img
                                            src={media.url}
                                            alt={media.alt_text || media.name}
                                            className="w-full h-auto max-h-48 object-contain"
                                        />
                                    ) : media.type === 'video' ? (
                                        <video
                                            controls
                                            className="w-full h-auto max-h-48"
                                            src={media.url}
                                        >
                                            Your browser does not support the video tag.
                                        </video>
                                    ) : media.type === 'audio' ? (
                                        <div className="p-6 text-center">
                                            <Music className="h-12 w-12 mx-auto text-gray-400 mb-2" />
                                            <audio controls className="w-full">
                                                <source src={media.url} type={media.mime_type} />
                                                Your browser does not support the audio element.
                                            </audio>
                                        </div>
                                    ) : (
                                        <div className="p-8 text-center">
                                            <TypeIcon className="h-12 w-12 mx-auto text-gray-400 mb-2" />
                                            <p className="text-sm text-gray-600">Preview not available</p>
                                        </div>
                                    )}
                                </div>

                                <div className="space-y-3 text-sm">
                                    <div>
                                        <span className="font-medium text-gray-700">Original Name:</span>
                                        <p className="text-gray-900 break-all">{media.original_name}</p>
                                    </div>

                                    <div>
                                        <span className="font-medium text-gray-700">File Size:</span>
                                        <p className="text-gray-900">{media.size_for_humans}</p>
                                    </div>

                                    <div>
                                        <span className="font-medium text-gray-700">MIME Type:</span>
                                        <p className="text-gray-900">{media.mime_type}</p>
                                    </div>

                                    {media.width && media.height && (
                                        <div>
                                            <span className="font-medium text-gray-700">Dimensions:</span>
                                            <p className="text-gray-900">{media.width} × {media.height} pixels</p>
                                        </div>
                                    )}

                                    <div>
                                        <span className="font-medium text-gray-700">Uploaded:</span>
                                        <p className="text-gray-900">
                                            {new Date(media.created_at).toLocaleDateString()}
                                        </p>
                                    </div>

                                    <div>
                                        <span className="font-medium text-gray-700">Uploaded By:</span>
                                        <p className="text-gray-900">{media.uploader.name}</p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
