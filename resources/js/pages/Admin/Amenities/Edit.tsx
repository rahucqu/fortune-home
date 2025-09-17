import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/Admin/admin-layout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';

interface Amenity {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    icon: string | null;
    category: string | null;
    is_active: boolean;
    sort_order: number;
}

interface EditAmenityData {
    name: string;
    slug: string;
    description: string;
    icon: string;
    category: string;
    is_active: boolean;
    sort_order: number;
}

interface AmenitiesEditProps {
    amenity: Amenity;
}

export default function AmenitiesEdit({ amenity }: AmenitiesEditProps) {
    const { data, setData, put, processing, errors } = useForm<EditAmenityData>({
        name: amenity.name,
        slug: amenity.slug,
        description: amenity.description || '',
        icon: amenity.icon || '',
        category: amenity.category || '',
        is_active: amenity.is_active,
        sort_order: amenity.sort_order,
    });

    const breadcrumbs = [
        { title: 'Admin', href: '/admin' },
        { title: 'Amenities', href: '/admin/amenities' },
        { title: amenity.name, href: `/admin/amenities/${amenity.id}` },
        { title: 'Edit', href: `/admin/amenities/${amenity.id}/edit` },
    ];

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/admin/amenities/${amenity.id}`);
    };

    const amenityCategories = [
        'interior',
        'exterior',
        'security',
        'recreational',
        'utility',
        'parking',
        'accessibility',
        'other',
    ];

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit Amenity: ${amenity.name}`} />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Edit Amenity</h1>
                        <p className="text-muted-foreground">
                            Update the details for {amenity.name}
                        </p>
                    </div>
                    <Button variant="outline" asChild>
                        <Link href={`/admin/amenities/${amenity.id}`}>
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back to Amenity
                        </Link>
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Amenity Information</CardTitle>
                        <CardDescription>
                            Update the amenity details below.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div className="space-y-2">
                                    <Label htmlFor="name">Amenity Name</Label>
                                    <Input
                                        id="name"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        placeholder="e.g. Swimming Pool"
                                        required
                                    />
                                    {errors.name && (
                                        <p className="text-sm text-destructive">{errors.name}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="slug">Slug</Label>
                                    <Input
                                        id="slug"
                                        value={data.slug}
                                        onChange={(e) => setData('slug', e.target.value)}
                                        placeholder="e.g. swimming-pool"
                                    />
                                    {errors.slug && (
                                        <p className="text-sm text-destructive">{errors.slug}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="category">Category</Label>
                                    <Select
                                        value={data.category}
                                        onValueChange={(value) => setData('category', value)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select a category" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {amenityCategories.map((category) => (
                                                <SelectItem key={category} value={category}>
                                                    {category.charAt(0).toUpperCase() + category.slice(1)}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.category && (
                                        <p className="text-sm text-destructive">{errors.category}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="sort_order">Sort Order</Label>
                                    <Input
                                        id="sort_order"
                                        type="number"
                                        value={data.sort_order}
                                        onChange={(e) => setData('sort_order', parseInt(e.target.value) || 0)}
                                        placeholder="0"
                                        min="0"
                                    />
                                    {errors.sort_order && (
                                        <p className="text-sm text-destructive">{errors.sort_order}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="icon">Icon Class</Label>
                                    <Input
                                        id="icon"
                                        value={data.icon}
                                        onChange={(e) => setData('icon', e.target.value)}
                                        placeholder="e.g. fa-swimming-pool or lucide-waves"
                                    />
                                    {errors.icon && (
                                        <p className="text-sm text-destructive">{errors.icon}</p>
                                    )}
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="description">Description</Label>
                                <Textarea
                                    id="description"
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    placeholder="Describe this amenity and its benefits..."
                                    rows={3}
                                />
                                {errors.description && (
                                    <p className="text-sm text-destructive">{errors.description}</p>
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

                            <div className="flex items-center space-x-2">
                                <Button type="submit" disabled={processing}>
                                    <Save className="h-4 w-4 mr-2" />
                                    {processing ? 'Updating...' : 'Update Amenity'}
                                </Button>
                                <Button type="button" variant="outline" asChild>
                                    <Link href={`/admin/amenities/${amenity.id}`}>Cancel</Link>
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
