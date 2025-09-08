import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AdminLayout from '@/layouts/Admin/admin-layout';
import { Head, Link, router } from '@inertiajs/react';
import { 
    Archive, 
    Copy, 
    Download, 
    Edit, 
    Eye, 
    FileText, 
    Image as ImageIcon, 
    MoreHorizontal, 
    Music, 
    Plus, 
    Search, 
    Trash2, 
    Upload, 
    Video 
} from 'lucide-react';
import { useState } from 'react';

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

interface MediaIndexProps {
    media: {
        data: Media[];
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    stats: {
        total: number;
        images: number;
        documents: number;
        videos: number;
        audio: number;
    };
    filters: {
        search: string;
        type: string;
    };
}

const typeIcons = {
    image: ImageIcon,
    video: Video,
    audio: Music,
    document: FileText,
    archive: Archive,
};

const typeColors = {
    image: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    video: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    audio: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
    document: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    archive: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
};

export default function MediaIndex({ media, stats, filters }: MediaIndexProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [type, setType] = useState(filters.type || 'all');

    const breadcrumbs = [
        { title: 'Admin', href: '/admin' },
        { title: 'Media', href: '/admin/media' },
    ];

    const handleSearch = () => {
        router.get(
            route('admin.media.index'),
            {
                search,
                type: type === 'all' ? '' : type,
            },
            {
                preserveState: true,
                preserveScroll: true,
            }
        );
    };

    const handleReset = () => {
        setSearch('');
        setType('all');
        router.get(route('admin.media.index'));
    };

    const copyToClipboard = (url: string) => {
        navigator.clipboard.writeText(url);
        // You could add a toast notification here
    };

    const handleDelete = (mediaId: number) => {
        if (confirm('Are you sure you want to delete this media file?')) {
            router.delete(route('admin.media.destroy', mediaId));
        }
    };

    const getTypeIcon = (mediaType: string) => {
        const IconComponent = typeIcons[mediaType as keyof typeof typeIcons] || FileText;
        return <IconComponent className="h-4 w-4" />;
    };

    const getTypeColor = (mediaType: string) => {
        return typeColors[mediaType as keyof typeof typeColors] || typeColors.document;
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title="Media Library" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Media Library</h1>
                        <p className="text-muted-foreground">
                            Manage your media files and assets
                        </p>
                    </div>
                    <Link href={route('admin.media.create')}>
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            Upload Media
                        </Button>
                    </Link>
                </div>

                {/* Stats Cards */}
                <div className="grid gap-4 md:grid-cols-5">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Files</CardTitle>
                            <FileText className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.total}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Images</CardTitle>
                            <ImageIcon className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.images}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Videos</CardTitle>
                            <Video className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.videos}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Audio</CardTitle>
                            <Music className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.audio}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Documents</CardTitle>
                            <Archive className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.documents}</div>
                        </CardContent>
                    </Card>
                </div>

                {/* Filters */}
                <Card>
                    <CardHeader>
                        <CardTitle>Filter Media</CardTitle>
                        <CardDescription>
                            Search and filter your media files
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="flex flex-col gap-4 md:flex-row md:items-end">
                            <div className="flex-1">
                                <label htmlFor="search" className="text-sm font-medium">
                                    Search
                                </label>
                                <div className="relative">
                                    <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                    <Input
                                        id="search"
                                        placeholder="Search by name, description..."
                                        value={search}
                                        onChange={(e) => setSearch(e.target.value)}
                                        className="pl-9"
                                        onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                                    />
                                </div>
                            </div>
                            <div className="w-full md:w-48">
                                <label htmlFor="type" className="text-sm font-medium">
                                    Type
                                </label>
                                <Select value={type} onValueChange={setType}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Types</SelectItem>
                                        <SelectItem value="image">Images</SelectItem>
                                        <SelectItem value="video">Videos</SelectItem>
                                        <SelectItem value="audio">Audio</SelectItem>
                                        <SelectItem value="document">Documents</SelectItem>
                                        <SelectItem value="archive">Archives</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="flex gap-2">
                                <Button onClick={handleSearch}>
                                    <Search className="mr-2 h-4 w-4" />
                                    Search
                                </Button>
                                <Button variant="outline" onClick={handleReset}>
                                    Reset
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Media Grid */}
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle>Media Files</CardTitle>
                                <CardDescription>
                                    {media.total} total files
                                </CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        {media.data.length > 0 ? (
                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                                {media.data.map((item) => (
                                    <Card key={item.id} className="overflow-hidden">
                                        <div className="aspect-square relative bg-muted">
                                            {item.is_image ? (
                                                <img
                                                    src={item.url}
                                                    alt={item.alt_text || item.name}
                                                    className="h-full w-full object-cover"
                                                />
                                            ) : (
                                                <div className="flex h-full items-center justify-center">
                                                    {getTypeIcon(item.type)}
                                                </div>
                                            )}
                                            <div className="absolute top-2 right-2">
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger asChild>
                                                        <Button variant="secondary" size="icon" className="h-8 w-8">
                                                            <MoreHorizontal className="h-4 w-4" />
                                                            <span className="sr-only">Open menu</span>
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent align="end">
                                                        <DropdownMenuItem asChild>
                                                            <Link href={route('admin.media.show', item.id)}>
                                                                <Eye className="mr-2 h-4 w-4" />
                                                                View
                                                            </Link>
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem asChild>
                                                            <Link href={route('admin.media.edit', item.id)}>
                                                                <Edit className="mr-2 h-4 w-4" />
                                                                Edit
                                                            </Link>
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem 
                                                            onClick={() => copyToClipboard(item.url)}
                                                        >
                                                            <Copy className="mr-2 h-4 w-4" />
                                                            Copy URL
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem asChild>
                                                            <a href={item.url} download>
                                                                <Download className="mr-2 h-4 w-4" />
                                                                Download
                                                            </a>
                                                        </DropdownMenuItem>
                                                        <DropdownMenuSeparator />
                                                        <DropdownMenuItem 
                                                            onClick={() => handleDelete(item.id)}
                                                            className="text-destructive"
                                                        >
                                                            <Trash2 className="mr-2 h-4 w-4" />
                                                            Delete
                                                        </DropdownMenuItem>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </div>
                                            <div className="absolute top-2 left-2">
                                                <Badge 
                                                    variant="secondary" 
                                                    className={`text-xs ${getTypeColor(item.type)}`}
                                                >
                                                    {getTypeIcon(item.type)}
                                                    <span className="ml-1">{item.type}</span>
                                                </Badge>
                                            </div>
                                        </div>
                                        <CardContent className="p-4">
                                            <div className="space-y-2">
                                                <h3 className="font-medium truncate" title={item.name}>
                                                    {item.name}
                                                </h3>
                                                <div className="flex items-center justify-between text-sm text-muted-foreground">
                                                    <span>{item.size_for_humans}</span>
                                                    {item.width && item.height && (
                                                        <span>{item.width} × {item.height}</span>
                                                    )}
                                                </div>
                                                <div className="text-xs text-muted-foreground">
                                                    <div>By {item.uploader.name}</div>
                                                    <div>{new Date(item.created_at).toLocaleDateString()}</div>
                                                </div>
                                            </div>
                                        </CardContent>
                                    </Card>
                                ))}
                            </div>
                        ) : (
                            <div className="text-center py-12">
                                <Upload className="mx-auto h-12 w-12 text-muted-foreground" />
                                <h3 className="mt-4 text-lg font-semibold">No media files found</h3>
                                <p className="mt-2 text-muted-foreground">
                                    {filters.search || filters.type ? 
                                        'Try adjusting your search criteria.' : 
                                        'Upload your first media file to get started.'
                                    }
                                </p>
                                {!filters.search && !filters.type && (
                                    <Link href={route('admin.media.create')}>
                                        <Button className="mt-4">
                                            <Plus className="mr-2 h-4 w-4" />
                                            Upload Media
                                        </Button>
                                    </Link>
                                )}
                            </div>
                        )}

                        {/* Pagination */}
                        {media.data.length > 0 && media.last_page > 1 && (
                            <div className="mt-8 flex items-center justify-between">
                                <div className="text-sm text-muted-foreground">
                                    Showing {((media.current_page - 1) * media.per_page) + 1} to{' '}
                                    {Math.min(media.current_page * media.per_page, media.total)} of{' '}
                                    {media.total} results
                                </div>
                                <div className="flex items-center gap-2">
                                    {media.links.map((link, index) => {
                                        if (!link.url) {
                                            return (
                                                <span 
                                                    key={index} 
                                                    className="px-3 py-2 text-sm text-muted-foreground"
                                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                                />
                                            );
                                        }

                                        return (
                                            <Link
                                                key={index}
                                                href={link.url}
                                                className={`px-3 py-2 text-sm rounded-md transition-colors ${
                                                    link.active
                                                        ? 'bg-primary text-primary-foreground'
                                                        : 'hover:bg-muted'
                                                }`}
                                                dangerouslySetInnerHTML={{ __html: link.label }}
                                            />
                                        );
                                    })}
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
