import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AdminLayout from '@/layouts/Admin/admin-layout';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ArrowLeft, Edit, Calendar, Hash, Palette, Eye, EyeOff } from 'lucide-react';

interface Tag {
    id: number;
    name: string;
    slug: string;
    description?: string;
    color: string;
    seo_title?: string;
    seo_description?: string;
    seo_keywords?: string;
    is_active: boolean;
    sort_order: number;
    created_at: string;
    updated_at: string;
}

interface Props {
    tag: Tag;
}

export default function Show({ tag }: Props) {
    return (
        <AdminLayout>
            <Head title={`Tag: ${tag.name}`} />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Button variant="outline" size="sm" asChild>
                            <Link href={route('admin.tags.index')}>
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Back to Tags
                            </Link>
                        </Button>
                        <div>
                            <div className="flex items-center gap-3">
                                <h1 className="text-3xl font-bold tracking-tight">{tag.name}</h1>
                                <div 
                                    className="w-6 h-6 rounded-full border-2 border-border" 
                                    style={{ backgroundColor: tag.color }}
                                />
                                <Badge variant={tag.is_active ? 'default' : 'secondary'}>
                                    {tag.is_active ? (
                                        <>
                                            <Eye className="h-3 w-3 mr-1" />
                                            Active
                                        </>
                                    ) : (
                                        <>
                                            <EyeOff className="h-3 w-3 mr-1" />
                                            Inactive
                                        </>
                                    )}
                                </Badge>
                            </div>
                            <p className="text-muted-foreground">
                                Tag details and configuration
                            </p>
                        </div>
                    </div>
                    <Button asChild>
                        <Link href={route('admin.tags.edit', tag.id)}>
                            <Edit className="h-4 w-4 mr-2" />
                            Edit Tag
                        </Link>
                    </Button>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div className="lg:col-span-2 space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Tag Information</CardTitle>
                                <CardDescription>
                                    Basic details about this tag
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Name</label>
                                        <p className="text-sm font-medium">{tag.name}</p>
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Slug</label>
                                        <p className="text-sm font-mono bg-muted px-2 py-1 rounded">
                                            {tag.slug}
                                        </p>
                                    </div>
                                </div>

                                {tag.description && (
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Description</label>
                                        <p className="text-sm">{tag.description}</p>
                                    </div>
                                )}

                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Color</label>
                                        <div className="flex items-center gap-2">
                                            <div 
                                                className="w-4 h-4 rounded-full border" 
                                                style={{ backgroundColor: tag.color }}
                                            />
                                            <span className="text-sm font-mono">{tag.color}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Sort Order</label>
                                        <p className="text-sm">{tag.sort_order}</p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {(tag.seo_title || tag.seo_description || tag.seo_keywords) && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>SEO Settings</CardTitle>
                                    <CardDescription>
                                        Search engine optimization configuration
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    {tag.seo_title && (
                                        <div>
                                            <label className="text-sm font-medium text-muted-foreground">SEO Title</label>
                                            <p className="text-sm">{tag.seo_title}</p>
                                            <p className="text-xs text-muted-foreground">
                                                {tag.seo_title.length} characters
                                            </p>
                                        </div>
                                    )}

                                    {tag.seo_description && (
                                        <div>
                                            <label className="text-sm font-medium text-muted-foreground">SEO Description</label>
                                            <p className="text-sm">{tag.seo_description}</p>
                                            <p className="text-xs text-muted-foreground">
                                                {tag.seo_description.length} characters
                                            </p>
                                        </div>
                                    )}

                                    {tag.seo_keywords && (
                                        <div>
                                            <label className="text-sm font-medium text-muted-foreground">SEO Keywords</label>
                                            <div className="flex flex-wrap gap-1">
                                                {tag.seo_keywords.split(',').map((keyword, index) => (
                                                    <Badge key={index} variant="outline" className="text-xs">
                                                        {keyword.trim()}
                                                    </Badge>
                                                ))}
                                            </div>
                                        </div>
                                    )}
                                </CardContent>
                            </Card>
                        )}

                        {/* Future: Post usage statistics */}
                        {/* <Card>
                            <CardHeader>
                                <CardTitle>Usage Statistics</CardTitle>
                                <CardDescription>
                                    Posts using this tag
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">0</div>
                                <p className="text-sm text-muted-foreground">posts tagged</p>
                            </CardContent>
                        </Card> */}
                    </div>

                    <div className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Tag Preview</CardTitle>
                                <CardDescription>
                                    How this tag appears in the frontend
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="flex items-center gap-2 p-3 border rounded-lg">
                                    <div 
                                        className="w-4 h-4 rounded-full border" 
                                        style={{ backgroundColor: tag.color }}
                                    />
                                    <span className="font-medium">{tag.name}</span>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Tag Details</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <div className="flex items-center gap-2 text-sm">
                                    <Hash className="h-4 w-4 text-muted-foreground" />
                                    <span className="text-muted-foreground">ID:</span>
                                    <span>{tag.id}</span>
                                </div>
                                
                                <div className="flex items-center gap-2 text-sm">
                                    <Palette className="h-4 w-4 text-muted-foreground" />
                                    <span className="text-muted-foreground">Color:</span>
                                    <span className="font-mono">{tag.color}</span>
                                </div>

                                <div className="flex items-center gap-2 text-sm">
                                    <Calendar className="h-4 w-4 text-muted-foreground" />
                                    <span className="text-muted-foreground">Created:</span>
                                    <span>{new Date(tag.created_at).toLocaleDateString()}</span>
                                </div>

                                <div className="flex items-center gap-2 text-sm">
                                    <Calendar className="h-4 w-4 text-muted-foreground" />
                                    <span className="text-muted-foreground">Updated:</span>
                                    <span>{new Date(tag.updated_at).toLocaleDateString()}</span>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Quick Actions</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-2">
                                <Button asChild className="w-full" variant="outline">
                                    <Link href={route('admin.tags.edit', tag.id)}>
                                        <Edit className="h-4 w-4 mr-2" />
                                        Edit Tag
                                    </Link>
                                </Button>
                                {/* Future: Link to posts with this tag */}
                                {/* <Button asChild className="w-full" variant="outline">
                                    <Link href={route('admin.posts.index', { tag: tag.id })}>
                                        <FileText className="h-4 w-4 mr-2" />
                                        View Tagged Posts
                                    </Link>
                                </Button> */}
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
